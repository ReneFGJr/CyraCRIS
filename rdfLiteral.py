import database, sys, helper_nbr

def find(name, Class=0):
    name = name.replace("'","´")
    cp = "d_r1, id_n, n_name, n_lang"
    query = "SELECT {} FROM rdf_literal ".format(cp)
    query += "JOIN rdf_data ON rdf_literal.id_n = rdf_data.d_literal "
    query += "JOIN rdf_concept on d_r1 = id_cc"
    query += " WHERE n_name = '{}'".format(name)

    if Class > 0:
        query += " AND cc_class = {}".format(Class)
    query += " ORDER BY n_name ASC"
    rows = database.query(query)

    if rows == []:
        rows = findLike(name, Class)

    return rows


def tem_letras(texto: str) -> bool:
    """
    Retorna True se existir pelo menos uma letra na string.
    """
    return any(char.isalpha() for char in texto)


def findExact(name, Class=0):
    name = name.replace("'","´")
    cp = "d_r1, id_n, n_name, n_lang"
    query = "SELECT {} FROM rdf_literal ".format(cp)
    query += "JOIN rdf_data ON rdf_literal.id_n = rdf_data.d_literal "
    query += "JOIN rdf_concept on d_r1 = id_cc"
    query += " WHERE n_name = '{}'".format(name)

    if Class > 0:
        query += " AND cc_class = {}".format(Class)
    query += " ORDER BY n_name ASC"
    rows = database.query(query)

    return rows

def findLike(name, Class=0):
    cp = "d_r1, id_n, n_name, n_lang"
    query = "SELECT {} FROM rdf_literal ".format(cp)
    query += "JOIN rdf_data ON rdf_literal.id_n = rdf_data.d_literal "
    query += "JOIN rdf_concept on d_r1 = id_cc"
    query += " WHERE n_name LIKE '%{}%'".format(name)
    if Class > 0:
        query += " AND cc_class = {}".format(Class)
    query += " ORDER BY n_name ASC"
    rows = database.query(query)

    if rows == []:
        rows = findLikeAll(name, Class)

    return rows

def findLikeAll(name, Class=0):
    name = helper_nbr.removeStopWords(name)
    term = name.split(' ')
    cp = "d_r1, id_n, n_name, n_lang"
    query = "SELECT {} FROM rdf_literal ".format(cp)
    query += "JOIN rdf_data ON rdf_literal.id_n = rdf_data.d_literal "
    query += "JOIN rdf_concept on d_r1 = id_cc"
    query += " WHERE "
    n = 0
    for t in term:
        if n > 0:
            query += " AND "
        query += "(n_name LIKE '%{}%') ".format(t)
        n += 1
    if Class > 0:
        query += " AND (cc_class = {})".format(Class)
    rows = database.query(query)
    return rows

def register(nome,lang,charset="utf8mb4"):
    nome = nome.replace("'","´")
    nome = nome.strip()
    if (nome == ''):
        return 0

    query = "SELECT * FROM rdf_literal WHERE n_name = '{}'".format(nome)
    rows = database.query(query)
    if len(rows) == 0:
        insert = "INSERT INTO rdf_literal (n_name,n_lock,n_lang,n_charset) VALUES ('"+nome+"',1,'"+lang+"','"+charset+"')"
        database.insert(insert)
        rows = database.query(query)
    id = rows[0][0]
    return id
