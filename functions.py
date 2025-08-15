import pandas as pd
import os, sys

# Caminho do arquivo
# file_path = "data/terceiros/br-capes-colsucup-prog-2021-2025-03-31.csv"

def openCSV(file):
    if not os.path.exists(file):
        print(f"❌ Arquivo não encontrado: {file}")
        return None

    try:
        df = pd.read_csv(file, encoding='latin1', sep=None, engine='python')
        return df
    except Exception as e:
        print(f"⚠ Erro ao ler o arquivo: {e}")
        return None



if __name__ == "__main__":
    df = openCSV(file_path)

    lista_programas = df.tolist()
    print(lista_programas)

    for programa in lista_programas:
        print(programa["NM_ENTIDADE_ENSINO"])
