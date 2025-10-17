import database

def removeID(id):
    query = "delete from lost where id_l = "+str(id)
    database.query(query)

def showLost(name: str = "", ltype: str = ""):
    """
    Retorna os registros da tabela 'lost'.
    Se informado 'name' ou 'ltype', aplica filtro.
    """
    query = "SELECT id_l, l_name, l_type, created_at FROM lost WHERE 1=1"

    if name != "":
        query += " AND l_name LIKE '%{}%'".format(name.replace("'", "´"))

    if ltype != "":
        query += " AND l_type = '{}'".format(ltype.replace("'", "´"))

    query += " ORDER BY l_name, created_at DESC"

    rows = database.query(query)
    return rows
