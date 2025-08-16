import database
import rdfClass, rdfData
import sys

def findConcept(name,Class):
    ClassID = rdfClass.getClass(Class)
    LiteralID = rdfData.conceptExists(name)

def getConcept(ID):
    cp = 'id_cc, c_class, cc_origin, n_name, n_lang, cc_use'
    query = "select {} from rdf_concept ".format(cp)
    query += " inner join rdf_class ON rdf_class.id_c = rdf_concept.cc_class "
    query += " inner join rdf_literal ON rdf_literal.id_n = rdf_concept.cc_pref_term "
    query += "where id_cc = '{}'".format(ID)
    row = database.query(query)
    if (row == []):
        return row

    # Uso da Remissiva
    if (row[0][5] > 0):
        return getConcept(row[0][5])

    dt = rdfData.getData(ID)

    dd = {
        'status': '200',
        'concept': {
        'ID': row[0][0],
        'Class': row[0][1],
        'Origin': row[0][2],
        'Name': row[0][3],
        'Lang': row[0][4],
        },
        'Data': dt
    }
    return dd

def getOrigin(Origin):
    if (Origin != ''):
        query = "select id_cc from rdf_concept where cc_origin = '{}'".format(Origin)
        row = database.query(query)
        if (row != []):
            return row[0][0]
        else:
            return 0
    else:
        return 0

def registerPrefLabel(concept_1, literal):
    concept_2 = 0
    prop = rdfClass.getClass('prefLabel')
    rdfData.register(concept_1,concept_2,prop,literal)

    query = "update rdf_concept set cc_pref_term = {} where id_cc = {}".format(literal, concept_1)
    database.update(query)

def register(Class, Origin = '', prefTerm = 0):
    IDClass = rdfClass.getClass(Class)
    if IDClass != 0:
        ######################## Verifica pela Origin
        idC = getOrigin(Origin)

        if (idC == 0):
            ######################## Verifica se não existe um conceito já registrado.
            insert = "INSERT INTO rdf_concept (cc_class, cc_use, c_equivalent, cc_pref_term, cc_origin) VALUES "
            insert += "({},0,0,{},'{}')".format(IDClass, prefTerm, Origin)
            database.insert(insert)
            idC = getOrigin(Origin)

        ############################ Tem termo Preferencial
        if (prefTerm > 0):
            registerPrefLabel(idC, prefTerm)
        return idC
    else:
        print("ERRO:Class not found:", Class)
        sys.exit(0)
