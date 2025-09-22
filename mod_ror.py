import requests
import helper_nbr

def buscar_instituicao_ror(nome):
    """
    Consulta uma instituição pelo nome na API do ROR.
    Retorna uma lista de dicionários com informações básicas.
    """
    url = "https://api.ror.org/organizations"
    url = "https://api.ror.org/v1/organizations"
    nome = helper_nbr.nbr_corporate(nome)
    params = {"query.advanced": '"'+nome+'"'}

    try:
        resp = requests.get(url, params=params, timeout=10)
        resp.raise_for_status()
        data = resp.json()

        resultados = []
        for item in data.get("items", []):
            if (item.get("name") == nome):
                resultados.append({
                    "id": item.get("id"),
                    "nome": item.get("name"),
                    "pais": item.get("country", {}).get("country_name"),
                    "acronimo": item.get("acronyms"),
                    "aliases": item.get("aliases")
                })
        return resultados

    except requests.exceptions.RequestException as e:
        print("Erro na consulta:", e)
        return []

if __name__ == "__main__":
    # Teste da função
    nome_instituicao = "Universidade Federal do Rio Grande do Sul"
    resultados = buscar_instituicao_ror(nome_instituicao)
    for r in resultados:
        print(r)
