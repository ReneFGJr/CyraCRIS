from zeep import Client
import base64
import os
import time
import zipfile

def get_file(id_lattes, destino="_lattes"):
    """
    Baixa o currículo Lattes via WebService SOAP do CNPq,
    salva o ZIP, cria uma pasta para o ID e extrai o XML.
    """

    # -------------------------
    # Diretórios e arquivos
    # -------------------------
    zip_name = f"lattes{id_lattes}.zip"
    zip_path = os.path.join(destino, zip_name)

    pasta_xml = os.path.join(destino, f"{id_lattes}_xml")
    os.makedirs(destino, exist_ok=True)
    os.makedirs(pasta_xml, exist_ok=True)

    # -------------------------
    # SOAP
    # -------------------------
    wsdl = "http://servicosweb.cnpq.br/srvcurriculo/WSCurriculo?wsdl"
    print(f"📡 Baixando currículo {id_lattes}...")

    client = Client(wsdl=wsdl)
    response = client.service.getCurriculoCompactado(id=id_lattes)

    # -------------------------
    # Decodifica Base64
    # -------------------------
    try:
        binario = base64.b64decode(response)
    except Exception as e:
        raise Exception(f"❌ Erro ao decodificar base64: {e}")

    # -------------------------
    # Salva o arquivo ZIP
    # -------------------------
    with open(zip_path, "wb") as f:
        f.write(binario)

    print(f"💾 ZIP salvo: {zip_path}")

    # -------------------------
    # Tenta abrir como ZIP
    # -------------------------
    if not zipfile.is_zipfile(zip_path):
        raise Exception("❌ O arquivo baixado NÃO é um ZIP válido!")

    # -------------------------
    # Extrai o XML
    # -------------------------
    print("📂 Extraindo XML...")
    with zipfile.ZipFile(zip_path, "r") as zip_ref:
        zip_ref.extractall(pasta_xml)

    print(f"✅ Extração concluída em: {pasta_xml}")

    time.sleep(0.5)

    return pasta_xml
