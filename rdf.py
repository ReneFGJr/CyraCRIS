import rdfConcept, rdfLiteral, rdfLost
import database, helper_nbr

def addRemissive(de,para):
    query = "update rdf_concept set cc_use = '{}' where id_cc = '{}'".format(para,de)
    database.update(query)
    return {"status": "200"}

def search(name,Class="*"):
    name = helper_nbr.nbr_corporate(name)
    print(name)
    dt = rdfLiteral.find(name)
    total = len(dt)
    if (total != 1):
        Class = 'orgUnit'
        rdfLost.register(name, Class)

    return dt

def c(ID):
    dt = rdfConcept.getConcept(ID)
    return dt