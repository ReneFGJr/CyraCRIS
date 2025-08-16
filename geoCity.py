import database, sys
import helper_nbr, rdfConcept, rdfLiteral, rdfData, rdfClass

def getCityId(city, state):
    name = city + ' (' + state+')'
    Class = rdfClass.getClass('City')
    idCity = rdfLiteral.find(name, Class)
    print("getCityId:", name, idCity)
    sys.exit()
    return idCity

def register(city,state='',country='Brasil',lang='pt'):
    city = helper_nbr.nbr_corporate(city)

    if (len(state) == 2):
        cityS = city
        city += ' (' + state + ')'

    # Registra Literal
    idCity = rdfLiteral.register(city,lang)

    Class = rdfClass.getClass('City')
    idC = rdfData.conceptExistsClass(idCity, Class)

    if idC == 0:
        print("  Cidade",city,state,country)
        hash = helper_nbr.crc32_hex(city + state)
        idC = rdfConcept.register('City','GEO:' + hash, idCity)

        if city != cityS:
            prop = rdfClass.getClass('altLabel')
            idCities = rdfLiteral.register(cityS,lang)
            rdfData.register(idC,0,prop,idCities)

        idState = rdfLiteral.register(state,lang)
        idCountry = rdfLiteral.register(country,lang)

        hash2 = helper_nbr.crc32_hex(state)
        hash3 = helper_nbr.crc32_hex(country)

        idP = rdfConcept.register('Country','GEO:' + hash3, idCountry)
        idS = rdfConcept.register('State','GEO:' + hash2, idState)

        ############################### É estado do Pais
        prop = rdfClass.getClass('hasState')
        rdfData.register(idP, idS, prop, 0)

        ############################### É cidade do estado
        prop = rdfClass.getClass('hasCity')
        idC = rdfConcept.register('City','GEO:' + hash, idCity)
        rdfData.register(idS, idC, prop, 0)
    return idC