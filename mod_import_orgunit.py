import helper_nbr
import functions, orgUnit, sys
import geoCity, rdfClass, rdfData, rdfLiteral, time
import mod_lost

def import_lost(t='universidade'):
    data = mod_lost.showLost(t)

    for row in data:
        nome = row[1]
        id = row[0]
        register(nome)
        mod_lost.removeID(id)


def import_orgunit_lattes(file_path):
    df = functions.openCSV(file_path)
    ies_set = set()
    city_set = set()
    erro = 0
    tot = 0

    for index, row in df.iterrows():
        nome = row['NOME-INSTITUICAO-EMPRESA']
        register(nome)

def register(nome):
    erro = 0
    ies_set = []
    
    if '(' in nome:
        nome = nome.split('(')[0].strip()

    if '- ' in nome:
        pos = nome.find('- ')
        if pos < 10:
            nomeP = nome.split('- ', 1)[0].strip()
            nome = nome.split('- ', 1)[1].strip() if '- ' in nome else nome.strip()
            nome = nomeP + ' ' + nome
        else:
            nome = nome.split('- ', 1)[0].strip()

    nome = helper_nbr.nbr_corporate(nome)
    if nome == 'Nao Informado':
        return nome

    IES = nome
    ok = rdfLiteral.tem_letras(nome)
    if IES == '' or IES is None or IES == '-' or not ok:
        return "Erro"

    nome = IES.strip("'").strip(" ")

    dt = rdfLiteral.findExact(nome, 1)

    if (dt != []):
        #print("Já existe ", nome)
        return "[]"

    IDo = orgUnit.register(nome)
    print(IDo, nome)

    print("Fim do processamento, Erros:", erro)
    sys.exit()

