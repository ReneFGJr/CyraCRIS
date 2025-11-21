from zeep import Client
import base64
import os
import time

def get_file(id_lattes, destino="_lattes"):
    """
    Baixa o currículo Lattes via WebService SOAP do CNPq.
    Equivalente ao código PHP fornecido.
    """

    # Nome e diretório do arquivo
    filename = f"lattes{id_lattes}.zip"
    os.makedirs(destino, exist_ok=True)
    filepath = os.path.join(destino, filename)

    # URL WSDL oficial do CNPq
    wsdl = "http://servicosweb.cnpq.br/srvcurriculo/WSCurriculo?wsdl"

    print(f"📡 Acessando WebService SOAP do CNPq para {id_lattes}...")

    # Cria o cliente SOAP
    client = Client(wsdl=wsdl)

    # Chamada SOAP (equivalente ao __call do PHP)
    response = client.service.getCurriculoCompactado(id=id_lattes)

    # A resposta vem em BASE64 -> precisa decodificar
    try:
        binario = base64.b64decode(response)
    except Exception as e:
        raise Exception(f"Erro ao decodificar base64: {e}")

    # Grava o ZIP
    with open(filepath, "wb") as f:
        f.write(binario)

    time.sleep(0.5)

    print(f"✅ Arquivo salvo em: {filepath}")
    return filepath
