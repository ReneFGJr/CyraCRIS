import database, sys, helper_nbr
import rdfLiteral, rdfConcept, rdfClass, rdfData

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