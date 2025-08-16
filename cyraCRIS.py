import sys
import capes_import_orgunit
import rdf, orgUnit
import database

def header():
    print("CyraCRIS Tools    v0.25.08.15")
    print("=============================")

if __name__ == "__main__":
    args = sys.argv[1:]  # Captura argumentos da linha de comando

    header()

    if (len(args) < 1):
        print("use: python cyraCRIS.py VERB PARM1 PARM2 ....")
        sys.exit(1)

    if (args[0] == "import"):
        if (args[1] == 'capes'):
            file_path = "data/terceiros/br-capes-colsucup-prog-2023-2025-03-31.csv"
            capes_import_orgunit.import_orgunit(file_path)

        elif (args[1] == 'emec'):
            file_path = "data/terceiros/portal-e-mec-graduacao.csv"
            capes_import_orgunit.import_orgunit_emec(file_path)

    ####################### Recupera Elemento
    elif (args[0] == "zerar"):
        database.query('TRUNCATE `rdf_concept`')
        database.query('TRUNCATE `rdf_data`')
        database.query('TRUNCATE `rdf_literal`')
        print("Database Zerada")
    ####################### Recupera Elemento
    elif (args[0] == "c"):
        json_rdf = rdf.c(args[1])
        print(json_rdf)
    elif (args[0] == "addOrgUnit"):
        json_rdf = orgUnit.add(args[1])
        print(json_rdf)
    ########################################################## SEARCH
    elif (args[0] == "search"):
        json_rdf = rdf.search(args[1])
        print(json_rdf)
    ########################################################## ADD REMISSIVE
    elif (args[0] == "addRemissive"):
        json_rdf = rdf.addRemissive(args[1],args[2])
        print(json_rdf)
    else:
        print("Unknown command:", args[0])
        sys.exit(1)
