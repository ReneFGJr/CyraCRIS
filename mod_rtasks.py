import database
import sys, time
import mod_ror
import rdfLiteral, rdfConcept, rdfClass, rdfData

def ror():
    action = 'ROR'
    sql = "SELECT id_cc as ID, cc_pref_term as IDp, n_name as NAME, cc_origin as ORIGIN, rp_status as STATUS "
    sql = sql + " FROM rdf_concept "
    sql = sql + " LEFT JOIN rdf_literal ON cc_pref_term = id_n "
    sql = sql + " LEFT JOIN rdt_process ON rp_concept = id_cc and rp_action = '{action}' ".format(action=action)
    sql = sql + " WHERE cc_class = 1 "
    sql = sql + " AND cc_use = 0 "
    sql = sql + " AND rp_status is NULL "
    sql = sql + " AND (n_name like 'Universidade%') "
    sql = sql + " ORDER BY n_name ASC"
    rows = database.query(sql)

    for i, row in enumerate(rows):
        id = row[0]
        name = row[2]
        origin = row[3]
        status = row[4]

        print(i+1, name, origin, status)
        ror = mod_ror.buscar_instituicao_ror(name)
        if (len(ror) == 0):
            print("  ROR Nao encontrado")
            time.sleep(1)
            updateStatus(id, action, 1)
            continue
        name2 = ror[0]['nome']
        url2 = ror[0]['id']
        country = ror[0]['pais']

        if (name.upper() == name2.upper()):
            print("  ============ROR=>:", ror[0]['id'], ror[0]['nome'], ror[0]['pais'])
            updateStatus(id, action, 20, url2)
        else:
            print("  ROR encontrado (diferente):", ror[0]['id'], ror[0]['nome'], ror[0]['pais'])
            updateStatus(id, action, 21,'')
        time.sleep(1)
    return rows

def updateStatus(id, action, status, comment=''):
    sql = "SELECT * FROM rdt_process WHERE rp_concept = {} and rp_action = '{}'".format(id, action)
    rows = database.query(sql)
    if (len(rows) == 0):
        insert = "INSERT INTO rdt_process (rp_concept, rp_action, rp_status, rp_comment) VALUES ({},'{}','{}','{}')".format(id, action, status, comment)
        database.insert(insert)
    else:
        update = "UPDATE rdt_process set rp_status = '{}', rp_comment = '{}' WHERE rp_concept = {} and rp_action = '{}'".format(status, comment, id, action)
        database.query(update)
    return

if __name__ == "__main__":
    args = sys.argv[1:]  # Captura argumentos da linha de comando

    if (len(args) < 1):
        print("use: python mod_ror.py VERB PARM1 PARM2 ....")
        sys.exit(1)

    if (args[0] == "ror"):
        rows = ror()
        for row in rows:
            print(row)