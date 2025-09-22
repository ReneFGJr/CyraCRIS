import database
import sys

def getDataAll():
    cp = "d_r1 as c1, d_r2 as c2, c_class, n_name, n_lang"
    query = "SELECT "+cp+" FROM rdf_data "
    query += "inner join rdf_class ON d_p = id_c "
    query += " inner join rdf_literal ON d_literal = id_n "
    query += "WHERE d_literal > 0 "
    row1 = database.query(query)

    cp = "d_r1 as c1, d_r2 as c2, c_class, n_name, n_lang"
    query = "SELECT "+cp+" FROM rdf_data "
    query += "inner join rdf_class ON d_p = id_c "
    query += " inner join rdf_concept ON d_r2 = id_cc "
    query += " inner join rdf_literal ON cc_pref_term = id_n "
    query += "WHERE d_literal = 0 "
    row2 = database.query(query)

    cp = "d_r2 as c1, d_r1 as c2, c_class, n_name, n_lang"
    query = "SELECT "+cp+" FROM rdf_data "
    query += "inner join rdf_class ON d_p = id_c "
    query += " inner join rdf_concept ON d_r1 = id_cc "
    query += " inner join rdf_literal ON cc_pref_term = id_n "
    query += "WHERE  d_literal = 0 "
    row3 = database.query(query)

    row = row1 + row2 + row3

    if (row == []):
        return []
    else:
        dd = []
        for i in range(len(row)):
            dr = {
                'ID': row[i][1],
                'Property': row[i][2],
                'Name': row[i][3],
                'Lang': row[i][4]
            }
            dd.append(dr)
        return dd

def getData(ID):
    cp = "d_r1 as c1, d_r2 as c2, c_class, n_name, n_lang"
    query = "SELECT "+cp+" FROM rdf_data "
    query += "inner join rdf_class ON d_p = id_c "
    query += " inner join rdf_literal ON d_literal = id_n "
    query += "WHERE d_r1 = '{}'".format(ID)
    query += " AND d_literal > 0 "
    row1 = database.query(query)

    cp = "d_r1 as c1, d_r2 as c2, c_class, n_name, n_lang"
    query = "SELECT "+cp+" FROM rdf_data "
    query += "inner join rdf_class ON d_p = id_c "
    query += " inner join rdf_concept ON d_r2 = id_cc "
    query += " inner join rdf_literal ON cc_pref_term = id_n "
    query += "WHERE d_r1 = '{}'".format(ID)
    query += " AND d_literal = 0 "
    row2 = database.query(query)

    cp = "d_r2 as c1, d_r1 as c2, c_class, n_name, n_lang"
    query = "SELECT "+cp+" FROM rdf_data "
    query += "inner join rdf_class ON d_p = id_c "
    query += " inner join rdf_concept ON d_r1 = id_cc "
    query += " inner join rdf_literal ON cc_pref_term = id_n "
    query += "WHERE d_r2 = '{}'".format(ID)
    query += " AND d_literal = 0 "
    row3 = database.query(query)

    cp = str(ID) + " as c1, 0 as c2, 'altLabel' as c_class, n_name, n_lang"
    query = "SELECT "+cp+" FROM rdf_concept "
    query += " inner join rdf_literal ON cc_pref_term = id_n "
    query += " WHERE cc_use = '{}'".format(ID)
    row4 = database.query(query)

    row = row1 + row2 + row3 + row4

    if (row == []):
        return 0
    else:
        dd = []
        for i in range(len(row)):
            dr = {
                'ID': row[i][1],
                'Property': row[i][2],
                'Name': row[i][3],
                'Lang': row[i][4]
            }
            dd.append(dr)
        return dd

def conceptExistsClass(IDn,Class):
    query = "SELECT d_r1 "
    query += " FROM rdf_data "
    query += " INNER JOIN rdf_concept ON d_r1 = id_cc "
    query += " WHERE d_literal = '{}'".format(IDn)
    query += " AND cc_class = '{}'".format(Class)
    rows = database.query(query)
    if rows == []:
        return 0
    else:
        return rows[0][0]

def conceptExists(IDn):
    query = "SELECT d_r1 FROM rdf_data WHERE d_literal = '{}'".format(IDn)
    rows = database.query(query)
    if rows == []:
        return 0
    else:
        return rows[0][0]

def register(concept_1,concept_2,prop,literal):
    query = "select * from rdf_data where d_r1 = '{}' and d_r2 = '{}' and d_literal = '{}'".format(concept_1, concept_2, literal)
    rows = database.query(query)
    if (rows == []):
        insert = "INSERT INTO rdf_data (d_r1 , d_r2, d_p, d_literal) VALUES "
        insert += "({}, {}, '{}', '{}')".format(concept_1, concept_2, prop, literal)
        database.insert(insert)
    else:
        ID = rows[0][0]
        update = "UPDATE rdf_data SET d_p = '{}', d_literal = '{}' WHERE id_d = '{}'".format(prop, literal, ID)
        database.update(update)
