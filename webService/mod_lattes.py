import requests
import os

def baixar_lattes(id_lattes, destino="dados/xml"):
    """
    Baixa o currículo Lattes usando a API do ExtratorLattes.
    
    Parâmetros:
        id_lattes (str): Número do ID Lattes (ex: '0004072613292475')
        destino (str): Diretório onde o arquivo ZIP será salvo.

    Retorna:
        str: Caminho completo do arquivo ZIP salvo.
    """
    
    # URL da API do ExtratorLattes (GET)
    url = f"https://api.extratorlattes.com.br/v1/curriculo/{id_lattes}"
    
    # Garante que o diretório existe
    os.makedirs(destino, exist_ok=True)
    
    # Caminho completo para salvar
    zip_path = os.path.join(destino, f"{id_lattes}.zip")
    
    print(f"📡 Baixando Lattes {id_lattes} ...")
    
    # Requisição GET
    response = requests.get(url, stream=True)
    
    # Verifica retorno
    if response.status_code != 200:
        raise Exception(f"Erro ao acessar API: HTTP {response.status_code}")
    
    # Salva o ZIP
    with open(zip_path, "wb") as f:
        for bloco in response.iter_content(chunk_size=8192):
            if bloco:
                f.write(bloco)

    print(f"✅ Download concluído: {zip_path}")
    return zip_path
