import helper_nbr
import functions, database, sys


def import_orgunit2(file_path):
    df = functions.openCSV(file_path)
    ies_set = set()

    for _, row in df.iterrows():
        IES = row['NM_ENTIDADE_ENSINO']
        IES = helper_nbr.nbr_corporate(IES)  # normaliza o nome
        ies_set.add(IES)  # evita repetições

    # Gera lista ordenada alfabeticamente
    ies_list = sorted(ies_set)

    for idx, nome in enumerate(ies_list, start=1):
        print(f"{idx}: {nome}")


def import_orgunit(file_path):
    df = functions.openCSV(file_path)
    ies_set = set()

    for index, row in df.iterrows():
        IES = row['NM_ENTIDADE_ENSINO']
        IES = helper_nbr.nbr_corporate(IES)
        IES_SG = row['SG_ENTIDADE_ENSINO']
        IES_CAPES = str(row['CD_ENTIDADE_CAPES'])
        IES_CAPES_ORG = IES_CAPES[:5]

        NAME = IES + ";" + IES_SG + ";" + IES_CAPES + ";" + IES_CAPES_ORG
        ies_set.add(NAME)  # evita repetições

    # Gera lista ordenada alfabeticamente
    ies_list = sorted(ies_set)
    charset = "utf8mb4"
    lang = "pt"

    for idx, nome in enumerate(ies_list, start=1):
        data = nome.split(";")
        print(f"{idx}: {data}")
        nome = data[0].strip("'")
        query = "SELECT * FROM rdf_literal WHERE name = '{}'".format(nome)
        print("========="+query)
        rows = database.query(query)
        if len(rows) == 0:
            query = "INSERT INTO rdf_literal (n_name,n_lock,n_lang,n_charset) VALUES ('"+nome+"',1,'"+lang+"','"+charset+"')"
            print(query)
            print("Nenhum resultado encontrado.")
            sys.exit()
        print("===>",rows)
