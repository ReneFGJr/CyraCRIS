# app.py
from pathlib import Path
from flask import Flask, Response, send_file, render_template, request, jsonify
from flask_cors import CORS, cross_origin
import cyraCRIS, orgUnit
import rdf, functions
import rdfConcept
import rdfLiteral

# Se seu arquivo HTML está em "html/multi_api.html",
# torne "html" a pasta de templates:
app = Flask(__name__, template_folder="html")
# libera tudo (ajuste para sua origem em produção)
CORS(app, resources={r"/*": {"origins": "*"}})

# --- helpers -------------------------------------------------
def json_response(payload, status=200):
    """Garante JSON mesmo se vier string pronta."""
    if isinstance(payload, (dict, list)):
        return jsonify(payload), status
    # se já vier string JSON (ex.: de outra lib)
    return Response(payload, status=status, mimetype="application/json; charset=utf-8")

# --- rotas ---------------------------------------------------
@app.get("/")
def main():
    # Vai procurar html/multi_api.html
    return render_template("multi_api.html")

@app.get("/list")
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

# Ex.: GET /orgunit/12345678
@app.get("/orgunit/id/<org_id>")
def show(org_id: str):
    org_id = functions.sonumero(org_id)
    data = rdf.c(org_id)  # ajuste conforme sua função
    if not data:
        return jsonify({"status": 404, "message": "Not Found", "id": org_id}), 404
    return data

# Ex.: GET /orgunit/search?q=UFRGS
@app.get("/orgunit/search")
def search():
    q = (request.args.get("q") or "").strip()
    if not q:
        return jsonify({"status": 400, "message": "Missing query param 'q'"}), 400

    result = cyraCRIS.search(q)  # sua função deve devolver dict/list serializável
    # Caso sua search já retorne um dict com status/message/items, só repasse:
    return json_response(result, 200)

# (Opcional) Se quiser servir o HTML sem templates:
@app.get("/lost")
def lost():
    import mod_lost
    data = mod_lost.showLost()
    print(data)
    return json_response(data, 200)

# (Opcional) Se quiser servir o HTML sem templates:
@app.get("/tester2")
def tester2():
    return send_file(
        Path("html") / "multi_api.html",
        mimetype="text/html; charset=utf-8"
    )

#if __name__ == "__main__":
#    app.run(debug=True)
if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=False)
