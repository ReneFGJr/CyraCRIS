import database, sys, helper_nbr
import rdfLiteral, rdfConcept, rdfClass, rdfData

def list():
    sql = "SELECT id_cc as ID, cc_pref_term as IDp, n_name as NAME "
    sql = sql + " FROM rdf_concept "
    sql = sql + " LEFT JOIN rdf_literal ON cc_pref_term = id_n "
    sql = sql + " WHERE cc_class = 1 "
    sql = sql + " AND cc_use = 0 "
    sql = sql + " ORDER BY cc_pref_term ASC"
    rows = database.query(sql)
    return rows

def check():
    sql = "SELECT id_cc as ID, cc_pref_term as IDp, n_name as NAME "
    sql = sql + " FROM rdf_concept "
    sql = sql + " LEFT JOIN rdf_literal ON cc_pref_term = id_n "
    sql = sql + " WHERE cc_class = 1 "
    sql = sql + " AND cc_use = 0 "
    sql = sql + " AND n_name like '%(%'"
    sql = sql + " ORDER BY cc_pref_term ASC"
    rows = database.query(sql)

    for i, row in enumerate(rows):
        id = row[0]
        name = row[2]
        dataR = rdfLiteral.find(name,1)
        tot = len(dataR)

        print(id,name,tot,dataR,'\r\n')
        if (tot == 1):
            name2 = dataR[0][2]
            if '(' in name2:
                name2 = name2.split('(')[0].strip()

                idn = str(dataR[0][1])
                sql = "update rdf_literal set n_name = '"+name2+"' where id_n = "+idn
                database.query(sql)

                idN = rdfLiteral.register(name2, "pt", "utf8mb4")
                rdfData.register(id, 0, rdfClass.getClass('altLabel'), idN)
                print("    OK",idN)
        return row
    return []


def format(name):
    orgunit = f"BRCRIS-{int(name):08d}"
    return orgunit

def add(name):
    name = helper_nbr.nbr_corporate(name.strip())
    idN = rdfLiteral.register(name, "pt", "utf8mb4")

    ##################################### Verifica se já não existe um conceito
    IDc = rdfData.conceptExists(idN)

    if (IDc == 0):
        Origin = 'MANUAL:' + helper_nbr.crc32_hex(name)
        IDc = rdfConcept.register("CorporateBody", Origin, idN)

    dd = {'status':'200','id': IDc}
    return dd

def register(nome, sigla, capes, capes_org):
    ##################################### Registra Nomes
    idN = rdfLiteral.register(nome, "pt", "utf8mb4")
    idSigla = rdfLiteral.register(sigla, "pt", "utf8mb4")
    idCapes = rdfLiteral.register(capes, "pt", "utf8mb4")
    idCapesOrg = rdfLiteral.register(capes_org, "pt", "utf8mb4")

    ##################################### Registra Conceitos OrgUnit
    IDc = rdfConcept.register("CorporateBody", capes, idN)

    ################### Propriedades
    prop = rdfClass.getClass('hasAcronym')
    rdfData.register(IDc, 0, prop, idSigla)

    ################### Capes
    rdfData.register(IDc, 0, prop, idSigla)
    rdfData.register(IDc, 0, prop, idSigla)

    return IDc