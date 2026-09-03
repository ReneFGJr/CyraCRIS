# Documentação - Tabela Person

## ✓ Estrutura Criada

### 1. Migration: `CreatePerson`
**Arquivo**: `app/Database/Migrations/20260903000000_CreatePerson.php`

Cria a tabela `person` com as seguintes colunas:
- `id` (BIGINT, auto_increment, primary key)
- `name` (VARCHAR 255, required)
- `email` (VARCHAR 255, unique, nullable)
- `lattes_url` (VARCHAR 500, nullable)
- `lattes_id` (VARCHAR 100, nullable)
- `cpf` (VARCHAR 20, nullable)
- `created_at` (TIMESTAMP, auto)
- `updated_at` (TIMESTAMP, auto)

**Status**: ✓ Tabela criada com sucesso

---

### 2. Model: `PersonModel`
**Arquivo**: `app/Models/PersonModel.php`

Modelo CodeIgniter para a tabela `person` com:
- Validação de dados
- Timestamps automáticos
- Campos permitidos configurados
- Regras de validação (email único, URL válida, etc.)

**Uso**:
```php
$personModel = model('PersonModel');
$persons = $personModel->findAll();
$person = $personModel->find($id);
$personModel->insert(['name' => 'João', 'email' => 'joao@example.com']);
```

---

### 3. Seeders

#### A. PersonSeederExample
**Arquivo**: `app/Database/Seeds/PersonSeederExample.php`

Seeder com 5 dados de exemplo já inseridos na tabela.

**Executar (já foi executado)**:
```bash
php spark db:seed PersonSeederExample
```

---

#### B. PersonSeeder (Para importar dados do Excel)
**Arquivo**: `app/Database/Seeds/PersonSeeder.php`

Importa dados de um arquivo Excel.

**Como usar**:
1. Coloque o arquivo Excel em: `data/alunos_consolidados_com_lattes.xlsx`
2. Execute:
```bash
php spark db:seed PersonSeeder
```

---

### 4. Comando CLI: Import from Excel
**Arquivo**: `app/Commands/ImportPersonFromExcel.php`

Comando flexível para importar dados do Excel com mais opções.

**Como usar**:
```bash
# Usar arquivo padrão
php spark db:import-persons

# Usar arquivo customizado
php spark db:import-persons --file=caminho/do/seu/arquivo.xlsx
```

**Características**:
- Mostra os headers do arquivo
- Importação em lotes de 100 registros
- Pula linhas vazias automaticamente
- Valida e limpa os dados
- Relatório final com total importado

---

## 📋 Dados Atualmente na Tabela

A tabela foi populada com 5 registros de exemplo:
1. Dr. João Silva - joao.silva@example.com
2. Dra. Maria Santos - maria.santos@example.com
3. Prof. Carlos Oliveira - carlos.oliveira@example.com
4. Profa. Ana Costa - ana.costa@example.com
5. Dr. Ricardo Pereira - ricardo.pereira@example.com

---

## 📁 Estrutura do Arquivo Excel

O arquivo Excel deve ter as seguintes colunas (nesta ordem):

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| 0 | String | Nome da pessoa (obrigatório) |
| 1 | String | Email (opcional) |
| 2 | String | URL Lattes (opcional) |
| 3 | String | ID Lattes (opcional) |
| 4 | String | CPF (opcional) |

**Exemplo de arquivo Excel válido**:
```
Nome | Email | Lattes URL | Lattes ID | CPF
Dr. João Silva | joao@example.com | http://lattes.cnpq.br/xxx | 1234567890123456 | 123.456.789-00
Dra. Maria Santos | maria@example.com | http://lattes.cnpq.br/yyy | 9876543210987654 | 987.654.321-00
```

---

## 🚀 Próximos Passos

1. **Colocar o arquivo Excel** na pasta `data/alunos_consolidados_com_lattes.xlsx`
2. **Executar o comando de importação**:
   ```bash
   php spark db:import-persons
   ```
3. **Verificar os dados** usando o Model:
   ```php
   $personModel = model('PersonModel');
   $allPersons = $personModel->findAll();
   ```

---

## 🔧 Troubleshooting

### "Arquivo não encontrado"
- Certifique-se de que o arquivo Excel está em `data/alunos_consolidados_com_lattes.xlsx`
- Use a opção `--file` se o arquivo está em outro local

### Erros de validação
- Verifique se o email é válido (se fornecido)
- Verifique se a URL do Lattes é válida (se fornecida)
- O campo "name" é obrigatório

### Emails duplicados
- Se houver emails duplicados, a importação falhará pois email é único
- Remova duplicatas do Excel antes de importar

---

## 📚 Comandos Úteis

```bash
# Ver todas as pessoas
php spark db:table person

# Limpar a tabela
php spark db:wipe person

# Executar migração específica
php spark migrate --namespace App

# Rollback da migration
php spark migrate:rollback --namespace App
```

---

**Criado em**: 2026-09-03
**Versão**: 1.0
**Status**: ✓ Pronto para usar
