import database, sys

Classes = {}

def getClass(ClassName):
    if ClassName in Classes:
        return Classes[ClassName]
    query = "SELECT * FROM rdf_class WHERE c_class = '{}'".format(ClassName)
    result = database.query(query)
    if (len(result) == 0):
        print("Class not found:", ClassName)
        sys.exit()
        return 0
    Classes[ClassName] = result[0][0]
    return result[0][0]
