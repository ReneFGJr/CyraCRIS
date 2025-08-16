import helper_nbr
import functions, orgUnit, sys
import geoCity, rdfClass, rdfData, rdfLiteral

def import_orgunit_emec(file_path):

    df = functions.openCSV(file_path)
    ies_set = set()
    city_set = set()
    erro = 0

    for index, row in df.iterrows():
        nome = row['INSTITUICAO']
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
        sigla = row['SIGLA']
        cidade = row['CIDADE']
        UF = row['UF']
        COD = row['COD_IES']
        SITE = row['SITE']

        ################# Instituições
        try:
            name = nome+";"+sigla+";"+cidade+";"+UF+";"+str(COD)+";"+SITE
            ies_set.add(name)  # evita repetições
        except Exception as e:
            erro += 1

        ################# Cidades
        try:
            city = cidade + ';' + UF
            city_set.add(city)  # evita repetições
        except Exception as e:
            erro += 1

    print("Processando instituições...")
    ies_set = sorted(ies_set)
    propLoc = rdfClass.getClass('isLocatedIn')

    for IES in ies_set:
        ies = IES.split(';')

        nome = ies[0].strip("'")
        sigla = ies[1].strip("'")
        capes = 'MEC:'+str(ies[4]).strip("'")
        capes_org = capes
        cidade = ies[2].strip("'")
        UF = ies[3].strip("'")

        IDo = orgUnit.register(nome, sigla, capes, capes_org)
        print(IDo, nome, sigla, cidade, UF, capes, capes_org)

        idCity = geoCity.register(cidade, UF)
        print("Cidade ===>",idCity,cidade,UF)
        rdfData.register(IDo,idCity,propLoc,0)




    sys.exit()


def import_orgunit(file_path):
    df = functions.openCSV(file_path)
    ies_set = set()

    for index, row in df.iterrows():
        IES = row['NM_ENTIDADE_ENSINO']
        IES = helper_nbr.nbr_corporate(IES)
        IES_SG = row['SG_ENTIDADE_ENSINO']
        IES_CAPES = str(row['CD_ENTIDADE_CAPES'])
        IES_CAPES_ORG = IES_CAPES[:5]

        NAME = IES + ";" + IES_SG + ";" + IES_CAPES + ";" + IES_CAPES_ORG
        ies_set.add(NAME)  # evita repetições

    # Gera lista ordenada alfabeticamente
    ies_list = sorted(ies_set)
    charset = "utf8mb4"
    lang = "pt"

    Class = rdfClass.getClass('CorporateBody')

    for idx, nome in enumerate(ies_list, start=1):
        data = nome.split(";")
        nome = data[0].strip("'")
        sigla = data[1].strip("'")
        capes = data[2].strip("'")
        capes_org = data[3].strip("'")

        RST = rdfLiteral.find(nome,Class)
        if RST:
            print("Já existe ",nome)
        else:
            IDc = orgUnit.register(nome, sigla, capes, capes_org)
            print("##",nome, sigla,IDc)
