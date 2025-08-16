import database

def register(name,Class):
    query = "SELECT * FROM lost WHERE l_name = '{}' AND l_type = '{}'".format(name, Class)
    rows = database.query(query)
    if rows == []:
        query = "insert into lost (l_name,l_type) values ('{}', '{}')".format(name, Class)
        database.insert(query)
    return True