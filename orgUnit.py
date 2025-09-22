import database, sys, helper_nbr
import rdfLiteral, rdfConcept, rdfClass, rdfData
import functions

def saveUSE(org_id, ids):
    org_id = int(org_id)
    if (org_id == 0):
        return {"status":"400","message":"OrgUnit ID inválido"}

    for id in ids:
        id = int(functions.sonumero(id))
        if (id == 0):
            continue
        if (id == org_id):
            continue

        sql = "update rdf_concept set cc_use = "+str(org_id)+" where id_cc = "+str(id)
        database.query(sql)

    return {"status":"200","message":f"{len(ids)} registros atualizados"}

def list():
    sql = "SELECT id_cc as ID, cc_pref_term as IDp, n_name as NAME, cc_origin as ORIGIN "
    sql = sql + " FROM rdf_concept "
    sql = sql + " LEFT JOIN rdf_literal ON cc_pref_term = id_n "
    sql = sql + " WHERE cc_class = 1 "
    sql = sql + " AND cc_use = 0 "
    sql = sql + " ORDER BY n_name ASC"
    rows = database.query(sql)
    return rows

def check():
    sql = "SELECT id_cc as ID, cc_pref_term as IDp, n_name as NAME "
    sql = sql + " FROM rdf_concept "
    sql = sql + " LEFT JOIN rdf_literal ON cc_pref_term = id_n "
    sql = sql + " WHERE cc_class = 1 "
    sql = sql + " AND cc_use = 0 "
    sql = sql + " AND n_name like '%(%'"
    sql = sql + " ORDER BY n_name ASC"
    rows = database.query(sql)

    for i, row in enumerate(rows):
        id = row[0]
        name = row[2]
        dataR = rdfLiteral.find(name,1)
        tot = len(dataR)

        if (tot == 1):
            name2 = dataR[0][2]
            name3 = name2
            if '(' in name2:
                name2 = name2.split('(')[0].strip()
                dataN = rdfLiteral.findExact(name2,1)

                if (len(dataN) == 0):
                    idn = str(dataR[0][1])
                    sql = "update rdf_literal set n_name = '"+name2+"' where id_n = "+idn
                    database.query(sql)

                    idN = rdfLiteral.register(name3, "pt", "utf8mb4")
                    Origin = 'R'+str(dataR[0][0])
                    rdfConcept.register("CorporateBody", Origin, idN)
                    print("Novo->",name3)
                    sys.exit()
                else:
                    print("ORG->",name2)
                    dataN = dataN[0]

                    sql = "update rdf_concept set cc_use = "+str(dataN[0])+" where id_cc = "+str(id)
                    database.query(sql)

                    idN = rdfLiteral.register(name3, "pt", "utf8mb4")
                    rdfData.register(id, 0, rdfClass.getClass('altLabel'), idN)
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

def register(nome, sigla='', capes='', capes_org=''):
    ##################################### Registra Nomes
    idSigla = 0
    idCapes = 0
    idCapesOrg = 0

    idN = rdfLiteral.register(nome, "pt", "utf8mb4")
    if (sigla != '' and sigla is not None):
        idSigla = rdfLiteral.register(sigla, "pt", "utf8mb4")
    if (capes != '' and capes is not None):
        idCapes = rdfLiteral.register(capes, "pt", "utf8mb4")
    if (capes_org != '' and capes_org is not None):
        idCapesOrg = rdfLiteral.register(capes_org, "pt", "utf8mb4")
    if (capes == ''):
        capes = 'O:' + helper_nbr.hash(nome)

    ##################################### Registra Conceitos OrgUnit
    IDc = rdfConcept.register("CorporateBody", capes, idN)

    ################### Propriedades
    if (idSigla != 0):
        prop = rdfClass.getClass('hasAcronym')
        rdfData.register(IDc, 0, prop, idSigla)

    return IDc
