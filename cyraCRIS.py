import sys
import mod_import_orgunit
import mod_rtasks
import rdf, orgUnit
import database

def header():
    print("CyraCRIS Tools    v0.25.08.15")
    print("=============================")


def search(arg1, arg2=None):
    result = rdf.search(arg1, arg2) or []
    # Garante lista (caso a busca retorne 1 objeto ou um gerador)
    if not isinstance(result, (list, tuple)):
        result = [result]
    result = list(result)

    n = len(result)
    if n == 0:
        dt = {"status": 404, "message": "Not Found"}
    elif n == 1:
        orgunit = orgUnit.format(result[0][0])
        dt = {"status": 200, "message": "OK", "orgunit": orgunit}
    else:
        dt = {"status": 500, "message": "Multiple items found", "items": result}
    return dt


if __name__ == "__main__":
    args = sys.argv[1:]  # Captura argumentos da linha de comando

    header()

    if (len(args) < 1):
        print("use: python cyraCRIS.py VERB PARM1 PARM2 ....")
        sys.exit(1)

    if (args[0] == "import"):
        if (args[1] == 'lost'):
            mod_import_orgunit.import_lost("universidade")
            mod_import_orgunit.import_lost("facultade")
            mod_import_orgunit.import_lost("universitario")


    ####################### Recupera Elemento
    elif (args[0] == "zerar"):
        database.query('TRUNCATE `rdf_concept`')
        database.query('TRUNCATE `rdf_data`')
        database.query('TRUNCATE `rdf_literal`')
        print("Database Zerada")
    elif (args[0] == 'ror'):
        mod_rtasks.ror()
    elif (args[0] == 'exportOrgUnits'):
        print("Exportando OrgUnits...")
        orgUnit.orgunitsRDF()
    ####################### Recupera Elemento
    elif (args[0] == "c"):
        json_rdf = rdf.c(args[1])
        print(json_rdf)
    elif (args[0] == "addOrgUnit"):
        json_rdf = orgUnit.add(args[1])
        print(json_rdf)
    ########################################################## SEARCH
    elif (args[0] == "check"):
        json_rdf = orgUnit.check()
        print(json_rdf)
    ########################################################## SEARCH
    elif (args[0] == "search"):
        json_rdf = search(args[1], args[2] if len(args) > 2 else None)
        print(json_rdf)
    ########################################################## ADD REMISSIVE
    elif (args[0] == "addRemissive"):
        json_rdf = rdf.addRemissive(args[1],args[2])
        print(json_rdf)
    else:
        print("Unknown command:", args[0])
        sys.exit(1)
