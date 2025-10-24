# app.py
from pathlib import Path
from flask import Flask, Response, send_file, render_template, request, jsonify, render_template_string, redirect, url_for
from flask_cors import CORS, cross_origin
import cyraCRIS, orgUnit
import rdf, functions
import rdfConcept
import rdfLiteral
from flasgger import Swagger
import mod_lost

# Se seu arquivo HTML está em "html/multi_api.html",
# torne "html" a pasta de templates:
app = Flask(__name__, template_folder="html")
# libera tudo (ajuste para sua origem em produção)
CORS(app, resources={r"/*": {"origins": "*"}})

swagger = Swagger(app)

# --- helpers -------------------------------------------------
def json_response(payload, status=200):
    """Garante JSON mesmo se vier string pronta."""
    if isinstance(payload, (dict, list)):
        return jsonify(payload), status
    # se já vier string JSON (ex.: de outra lib)
    return Response(payload, status=status, mimetype="application/json; charset=utf-8")

# --- rotas ---------------------------------------------------
@app.get("/")
# Vai procurar html/multi_api.html
def main():
    # Vai procurar html/multi_api.html
    return render_template("multi_api.html")

@app.get("/apidoc")
# @descrition: Documentação dos endpoints das APIs
# @return: HTML com a documentação dos endpoints
def apidoc():
    import mod_apidoc
    return Response(mod_apidoc.gerar_documentacao_api("app.py"),
                    mimetype="text/html; charset=utf-8")

@app.get("/list")
# @descrition: Lista todas as unidades organizacionais
# @return: HTML com a lista de unidades organizacionais
def list():
    data = orgUnit.list()
    return render_template("listOrgUnit.html", data=data, format_id=orgUnit.format)


@app.post("/orgunit/selecionar")
def orgunit_selecionar():
    ids = request.form.getlist("ids")  # lista com vários IDs
    org_id = request.form.get("ID")
    # faça o que precisar com os IDs (batch, export, etc.)
    # Ex.: flash(f"{len(ids)} itens selecionados: {ids}")
    import orgUnit
    return orgUnit.saveUSE(org_id, ids)
    #return jsonify({"status": 200, "message": "API is running","data":ids,"org_id":org_id}), 200


@app.get("/orgunit/ulink/<org_id>")
def ulink(org_id: str):
    data = rdfConcept.getID(org_id)
    ID = data[0][1]
    rdfConcept.ulink(org_id)
    return redirect(f"/orgunit/v/{ID}")
    

@app.get("/orgunit/v/<org_id>")
def viewer(org_id: str):
    org_id = functions.sonumero(org_id)
    data = rdfConcept.getConcept(org_id)
    
    sx = render_template("header.html", data=data, format_id=orgUnit.format)
    sx += render_template("OrgUnit.html", data=data, format_id=orgUnit.format)

    # pega ?q= do GET; se não vazio, usa em 'name'
    q = request.args.get('q', '')
    q = q.strip() if q is not None else ''

    if q == '':
        name = data.get('concept', {}).get('name', '')
    else:
        name = q
    #name = "Universidade de Sao Paulo"
    data2 = rdfLiteral.findLike(name, 1, False)
    sx += render_template("listOrgUnitChecked.html",
                          data=data2,
                          org_id = org_id,
                          name = name,
                          format_id=orgUnit.format)

    return sx

@app.get("/status")
def status():
    return jsonify({"status": 200, "message": "API is running"}), 200

@app.get("/orgunits")
def orgunits():
    data = orgUnit.orgunits()
    return jsonify({"status": 200, "data": data}), 200

@app.get("/dump")
def dump():
    # @descrition: Exporta dados de unidades organizacionais em JSON (nome e código)
    # @return: JSON com a lista de unidades para conversão (DE) e (ID)
    data = orgUnit.orgunits_json()

    return jsonify(data), 200

@app.get("/orgUnitRDF")
def orgUnitRDF():
    # @descrition: Exporta dados de unidades organizacionais em RDF/JSON-LD
    # @return: JSON-LD com os dados RDF das unidades organizacionais
    orgunitsRDF = orgUnit.orgunits()
    return jsonify(orgunitsRDF), 200
    

# Ex.: GET /orgunit/12345678
@app.get("/orgunit/id/<org_id>")
def show(org_id: str):
    org_id = functions.sonumero(org_id)
    data = rdf.c(org_id)  # ajuste conforme sua função
    if not data:
        return jsonify({"status": 404, "message": "Not Found - ID", "id": org_id}), 404
    return data

# Ex.: GET /orgunit/search?q=UFRGS
@app.get("/orgunit/search")
# @descrition: Pesquisa unidades organizacionais por nome, variável '?q=termo'
# @return: JSON com a lista de unidades organizacionais encontradas
def search():
    q = (request.args.get("q") or "").strip()
    if not q:
        return jsonify({"status": 400, "message": "Missing query param 'q'"}), 400

    result = cyraCRIS.search(q)  # sua função deve devolver dict/list serializável
    # Caso sua search já retorne um dict com status/message/items, só repasse:
    return json_response(result, 200)

# (Opcional) Se quiser servir o HTML sem templates:
@app.get("/lost")
# @descrition: Mostra itens não localizados (lost) no CyraCRIS
# @return: JSON com a lista de itens lost
def lost():
    # Captura parâmetros GET
    name = request.args.get("name", "")
    ltype = request.args.get("ltype", "")

    # Busca no banco de dados via módulo mod_lost
    data = mod_lost.showLost(name, ltype)
    print(data)

    # Monta o HTML dinamicamente
    html = """
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>Itens Lost - CyraCRIS</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    </head>
    <body class="bg-light">
        <div class="container py-4">
            <h2 class="mb-4 text-primary">
                <i class="bi bi-exclamation-triangle"></i> Itens não localizados (Lost)
            </h2>

            <form method="get" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="name" value="{{ name }}" class="form-control" placeholder="Filtrar por nome">
                </div>
                <div class="col-md-3">
                    <input type="text" name="ltype" value="{{ ltype }}" class="form-control" placeholder="Filtrar por tipo">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
            </form>

            {% if data and data|length > 0 %}
            <table class="table table-striped table-hover table-bordered shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Data de Criação</th>
                    </tr>
                </thead>
                <tbody>
                    {% for row in data %}
                        <tr>
                            <td>{{ row[0] }}</td>
                            <td>{{ row[1] }}</td>
                            <td>{{ row[2] }}</td>
                            <td>{{ row[3] }}</td>
                        </tr>
                    {% endfor %}
                </tbody>
            </table>
            {% else %}
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i> Nenhum item encontrado.
                </div>
            {% endif %}
        </div>
    </body>
    </html>
    """

    return render_template_string(html, data=data, name=name, ltype=ltype)



#if __name__ == "__main__":
#    app.run(debug=True)
if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=False)
