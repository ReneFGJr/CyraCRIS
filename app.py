# app.py
from pathlib import Path
from flask import Flask, Response, send_file, render_template, request, jsonify
from flask_cors import CORS, cross_origin
import cyraCRIS
import rdf, functions

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

@app.get("/status")
def status():
    return jsonify({"status": 200, "message": "API is running"}), 200

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
@app.get("/tester2")
def tester2():
    return send_file(
        Path("html") / "multi_api.html",
        mimetype="text/html; charset=utf-8"
    )

if __name__ == "__main__":
    app.run(debug=True)
