<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use SimpleXMLElement;
use Throwable;

class Cerif extends BaseController
{
    private const SOURCE_URL = 'https://raw.githubusercontent.com/EuroCRIS/CERIF-DataModel/refs/heads/master/model/CERIF.txp';
    private const CACHE_KEY = 'cerif_model_v1';
    private const CACHE_TTL = 21600;
    private const MAX_XML_BYTES = 15 * 1024 * 1024;
    private const PAGE_SIZE = 15;

    public function index(): string|ResponseInterface
    {
        $query = mb_substr(trim((string) $this->request->getGet('q')), 0, 120);
        $type = (string) $this->request->getGet('tipo') === 'relacoes' ? 'relacoes' : 'entidades';
        $page = max(1, (int) $this->request->getGet('page'));

        try {
            $model = cache()->get(self::CACHE_KEY);
            if (! is_array($model) || ! isset($model['entities'], $model['relations'])) {
                $model = $this->loadModel();
                if (! cache()->save(self::CACHE_KEY, $model, self::CACHE_TTL)) {
                    log_message('warning', 'Não foi possível armazenar o modelo CERIF em cache.');
                }
            }
        } catch (Throwable $exception) {
            log_message('error', 'Falha ao carregar o modelo CERIF: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return $this->response->setStatusCode(503)->setBody(view('cerif/index', [
                'title'     => 'Modelo CERIF',
                'error'     => 'Não foi possível carregar o modelo CERIF agora. Tente novamente mais tarde.',
                'query'     => $query,
                'type'      => $type,
                'pageItems' => [],
                'page'      => 1,
                'pageCount' => 1,
                'total'     => 0,
                'counts'    => ['entities' => 0, 'relations' => 0, 'attributes' => 0, 'categories' => 0, 'workspaces' => 0],
                'version'   => '',
            ]));
        }

        $items = $type === 'relacoes' ? $model['relations'] : $model['entities'];
        if ($query !== '') {
            $term = mb_strtolower($query);
            $items = array_values(array_filter($items, static function (array $item) use ($term): bool {
                $searchable = implode(' ', [
                    $item['name'],
                    $item['caption'],
                    $item['description'],
                    $item['source'] ?? '',
                    $item['target'] ?? '',
                    implode(' ', array_column($item['attributes'] ?? [], 'name')),
                ]);

                return mb_strpos(mb_strtolower($searchable), $term) !== false;
            }));
        }

        usort($items, static fn (array $a, array $b): int => strcasecmp($a['name'], $b['name']));
        $total = count($items);
        $pageCount = max(1, (int) ceil($total / self::PAGE_SIZE));
        $page = min($page, $pageCount);

        return view('cerif/index', [
            'title'     => 'Modelo CERIF',
            'error'     => null,
            'query'     => $query,
            'type'      => $type,
            'pageItems' => array_slice($items, ($page - 1) * self::PAGE_SIZE, self::PAGE_SIZE),
            'page'      => $page,
            'pageCount' => $pageCount,
            'total'     => $total,
            'counts'    => $model['counts'],
            'version'   => $model['version'],
        ]);
    }

    /** @return array{entities: array<int, array<string, mixed>>, relations: array<int, array<string, mixed>>, counts: array<string, int>, version: string} */
    private function loadModel(): array
    {
        $response = service('curlrequest')->get(self::SOURCE_URL, [
            'headers'     => ['Accept' => 'application/xml, text/plain'],
            'http_errors' => false,
            'timeout'     => 30,
            'verify'      => env('auth.caBundle', true),
        ]);
        $xml = (string) $response->getBody();

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw new \RuntimeException('O repositório CERIF respondeu HTTP ' . $response->getStatusCode() . '.');
        }

        if ($xml === '' || strlen($xml) > self::MAX_XML_BYTES) {
            throw new \RuntimeException('O arquivo CERIF está vazio ou excede o limite de tamanho esperado.');
        }

        $previous = libxml_use_internal_errors(true);
        try {
            $root = simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOBLANKS);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if (! $root instanceof SimpleXMLElement || $root->getName() !== 'PERModelUN') {
            throw new \RuntimeException('O arquivo remoto não contém um modelo CERIF válido.');
        }

        $entitiesById = [];
        foreach ($root->Entities->PEREntityUN ?? [] as $entity) {
            $entitiesById[$this->xmlText($entity->Id ?? null)] = $entity;
        }

        $relationsById = [];
        foreach ($root->Relations->PERRelationUN ?? [] as $relation) {
            $relationsById[$this->xmlText($relation->Id ?? null)] = $relation;
        }

        $domainsById = [];
        foreach ($root->Domains->PERDomainUN ?? [] as $domain) {
            $domainsById[$this->xmlText($domain->Id ?? null)] = $this->xmlText($domain->Name ?? null);
        }

        $categoriesById = [];
        foreach ($root->Categories->CategoryUN ?? [] as $category) {
            $categoriesById[$this->xmlText($category->Id ?? null)] = $this->xmlText($category->Name ?? null);
        }

        $relations = [];
        foreach ($relationsById as $id => $relation) {
            $sourceId = $this->xmlText($relation->O1->Id ?? null);
            $targetId = $this->xmlText($relation->O2->Id ?? null);
            $relations[] = [
                'id'          => $id,
                'name'        => $this->xmlText($relation->Name ?? null),
                'caption'     => $this->xmlText($relation->Caption ?? null),
                'description' => $this->xmlText($relation->Comments ?? null) ?: $this->xmlText($relation->Notes ?? null),
                'source'      => $this->entityLabel($entitiesById[$sourceId] ?? null),
                'target'      => $this->entityLabel($entitiesById[$targetId] ?? null),
                'parent_required' => $this->xmlText($relation->MandatoryParent ?? null) === '1',
                'child_required'  => $this->xmlText($relation->MandatoryChild ?? null) === '1',
            ];
        }

        $entities = [];
        $attributeCount = 0;
        foreach ($entitiesById as $id => $entity) {
            $attributes = [];
            foreach ($entity->Attributes->PERAttributeUN ?? [] as $attribute) {
                $domainId = $this->xmlText($attribute->Domain->Id ?? null);
                $attributes[] = [
                    'name'     => $this->xmlText($attribute->Name ?? null),
                    'caption'  => $this->xmlText($attribute->Caption ?? null),
                    'domain'   => $domainsById[$domainId] ?? '',
                    'required' => $this->xmlText($attribute->NotNull ?? null) === '1',
                ];
            }
            $attributeCount += count($attributes);

            $related = [];
            foreach ($entity->Relations->Id ?? [] as $relationId) {
                $related[] = $relationsById[$this->xmlText($relationId)] ?? null;
            }

            $relationDetails = [];
            foreach ($related as $relation) {
                if (! $relation instanceof SimpleXMLElement) {
                    continue;
                }
                $sourceId = $this->xmlText($relation->O1->Id ?? null);
                $targetId = $this->xmlText($relation->O2->Id ?? null);
                $sourceName = $this->entityLabel($entitiesById[$sourceId] ?? null);
                $targetName = $this->entityLabel($entitiesById[$targetId] ?? null);
                $relationDetails[] = [
                    'name'   => $this->xmlText($relation->Caption ?? null) ?: $this->xmlText($relation->Name ?? null),
                    'source' => $sourceName,
                    'target' => $targetName,
                    'other'  => $sourceId === $id ? $targetName : $sourceName,
                ];
            }

            $entities[] = [
                'id'          => $id,
                'name'        => $this->xmlText($entity->Name ?? null),
                'caption'     => $this->xmlText($entity->Caption ?? null),
                'description' => $this->xmlText($entity->Comments ?? null) ?: $this->xmlText($entity->Notes ?? null),
                'category'    => $categoriesById[$this->xmlText($entity->Category->Id ?? null)] ?? '',
                'attributes'  => $attributes,
                'relations'   => $relationDetails,
            ];
        }

        if ($entities === [] || $relations === []) {
            throw new \RuntimeException('O arquivo CERIF não contém as entidades e os relacionamentos esperados.');
        }

        return [
            'entities'  => $entities,
            'relations' => $relations,
            'counts'    => [
                'entities'   => count($entities),
                'relations'  => count($relations),
                'attributes' => $attributeCount,
                'categories' => count($categoriesById),
                'workspaces' => count($root->WorkSpaces->PERWorkSpaceUN ?? []),
            ],
            'version'   => $this->xmlText($root->Name ?? null),
        ];
    }

    private function xmlText(?SimpleXMLElement $element): string
    {
        return $element === null ? '' : trim((string) $element);
    }

    private function entityLabel(?SimpleXMLElement $entity): string
    {
        if ($entity === null) {
            return 'Entidade desconhecida';
        }

        $name = $this->xmlText($entity->Name ?? null);
        $caption = $this->xmlText($entity->Caption ?? null);

        return $caption !== '' && $caption !== $name ? $caption . ' (' . $name . ')' : $name;
    }
}
