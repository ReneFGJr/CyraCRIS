import database, sys, helper_nbr
import rdfLiteral, rdfConcept, rdfClass, rdfData
import functions

import json, os

def orgunitsRDF(width: int = 8):
    # ===== 1. Consulta: organizações (prefLabel) =====
    query = """
    SELECT n_name, id_cc, cc_use 
    FROM rdf_concept 
    INNER JOIN rdf_literal AS l1 ON cc_pref_term = l1.id_n 
    WHERE cc_class = 1 AND cc_use = 0 
    ORDER BY n_name ASC
    """
    rows = database.query(query)

    orgs = {}

    for row in rows:
        name, IDorg, use = row
        IDorg = format(IDorg)
        orgs[IDorg] = {
            "@id": f"{IDorg}",
            "@type": "brcris:OrgUnit",
            "skos:prefLabel": name,
            "properties": []
        }

    # ===== 2. Consulta: altLabels =====
    query = """
    SELECT n_name, cc_use 
    FROM rdf_concept 
    INNER JOIN rdf_literal AS l1 ON cc_pref_term = l1.id_n 
    WHERE cc_class = 1 AND cc_use <> 0 
    ORDER BY n_name ASC
    """
    rows2 = database.query(query)

    for row in rows2:
        alt_name, IDalt = row
        IDalt = format(IDalt)

        IDalt = str(IDalt).zfill(width)

        if IDalt in orgs:
            orgs[IDalt]["properties"].append({"skos:altLabel": alt_name})
        else:
            print(f"[WARN] altLabel sem prefLabel: {IDalt} - {alt_name}")

    # ===== 3. Consulta: propriedades adicionais =====
    query = """
    SELECT d_r1, c_class, n_name, n_lang
    FROM rdf_data
    INNER JOIN rdf_concept ON d_r1 = id_cc
    INNER JOIN rdf_class ON d_p = id_c
    INNER JOIN rdf_literal ON d_literal = id_n
    WHERE d_r1 <> 0
    ORDER BY n_name ASC
    """
    rows3 = database.query(query)

    for row in rows3:
        IDprop, Class, Name, Lang = row
        IDprop = format(IDprop)

        if IDprop in orgs:
            orgs[IDprop]["properties"].append({Class: Name})

    # ===== 4. Monta grafo JSON-LD =====
    graph = []
    for org in orgs.values():
        node = {
            "@id": str(org["@id"]),
            "@type": org["@type"],
            "skos:prefLabel": org["skos:prefLabel"],
        }

        # Adiciona altLabels e propriedades extras
        for prop in org["properties"]:
            for k, v in prop.items():
                if k not in node:
                    node[k] = []
                node[k].append(v)

        graph.append(node)

# ===== 5. Define contexto JSON-LD =====
    rdf_json = {
        "@context": {
            "rdf": "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
            "rdfs": "http://www.w3.org/2000/01/rdf-schema#",
            "skos": "http://www.w3.org/2004/02/skos/core#",
            "brcris": "https://brcris.ibict.br/ontology/orgunit/"
        },
        "@graph": graph
    }

    # ===== 6. Salva em arquivo org.js =====

#    output_file = os.path.join('', "org.js")

#    # Adiciona prefixo para ser usado diretamente em JS
#    with open(output_file, "w", encoding="utf-8") as f:
#        f.write("const orgData = ")
#        json.dump(rdf_json, f, ensure_ascii=False, indent=2)
#        f.write(";\n\nexport default orgData;")

#    print(f"✅ Arquivo RDF JSON-LD salvo em: {output_file}")

    return rdf_json



def orgunits_json(width: int = 8):
    query = "SELECT n_name, id_cc, cc_use FROM `rdf_concept` "
    query += "INNER JOIN rdf_literal as l1 ON cc_pref_term = l1.id_n "
    query += "WHERE cc_class = 1 "
    query += "ORDER BY n_name ASC"
    rows = database.query(query)

    for i in range(len(rows)):
        name = rows[i][0]
        id_cc = rows[i][1]
        cc_use = rows[i][2]

        if (cc_use != 0):
            id_cc = cc_use
        try:
            code = format(f"{int(id_cc):0{width}d}")
        except (TypeError, ValueError):
            code = str(id_cc)

        rows[i] = {"code": code, "name": name}
    return rows

def orgunits(width: int = 8):
    #dataAll = rdfData.getDataAll()
    #return dataAll
    query = """
        SELECT id_cc, n_name, n_lang
        FROM rdf_concept
        INNER JOIN rdf_literal ON cc_pref_term = id_n
        WHERE cc_use = 0
        ORDER BY n_name ASC
    """
    rows = database.query(query)

    out = []
    for id_cc, n_name, n_lang in rows:
        # formata o ID como zero-padded (ex.: 00001234)
        try:
            code = format(f"{int(id_cc):0{width}d}")
        except (TypeError, ValueError):
            # fallback: apenas string
            code = str(id_cc)

        # logs opcionais
        # print((id_cc, n_name, n_lang))
        # print("*" * 20)
        # print(code)
        # print("*" * 20)

        out.append({"code": code, "name": n_name, "lang": n_lang,"data":{}})
    return out


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
