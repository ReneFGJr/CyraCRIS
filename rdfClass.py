import database, sys

def getClass(ClassName):
    query = "SELECT * FROM rdf_class WHERE c_class = '{}'".format(ClassName)
    result = database.query(query)
    if (len(result) == 0):
        print("Class not found:", ClassName)
        sys.exit()
        return 0
    return result[0][0]
