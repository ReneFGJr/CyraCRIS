# app.py
from flask import Flask, request, jsonify, abort
from werkzeug.exceptions import HTTPException
import cyraCRIS

app = Flask(__name__)

# Se for consumir via frontend (Angular/React), habilite CORS:
try:
    from flask_cors import CORS
    CORS(app,
         resources={
             r"/*": {
                 "origins": ["http://localhost:4200", "http://localhost:3000"]
             }
         })
except Exception:
    pass  # CORS é opcional; rode `pip install flask-cors` para usar

DB = {}  # "banco" em memória para demo


# ---- Handlers básicos ----
@app.get("/")
def main():
    return jsonify(status=200, message="API is running"), 200


@app.get("/status")
def status():
    return jsonify(status=200, message="Services OK"), 200


# Tratamento elegante de erros (JSON sempre)
@app.errorhandler(HTTPException)
def handle_http_error(e: HTTPException):
    return jsonify(error=e.name, message=e.description), e.code


@app.errorhandler(Exception)
def handle_unexpected(e: Exception):
    return jsonify(error="Internal Server Error", message=str(e)), 500


# ---- OrgUnit: CRUD simples ----
# GET /orgunit?name=...&limit=...
@app.get("/orgunit")
def search_orgunit():
    args = request.args.to_dict(flat=True)  # garante dict
    # Exemplos de filtros/paginação simples
    name = args.get("name", "").lower()
    limit = int(args.get("limit", 50))
    results = [
        v for v in DB.values()
        if not name or name in v.get("name", "").lower()
    ]
    return jsonify(count=len(results), items=results[:limit]), 200


# GET /orgunit/<id>
@app.get("/orgunit/<string:org_id>")
def get_orgunit(org_id: str):
    item = DB.get(org_id)
    if not item:
        abort(404, description="OrgUnit não encontrada {}".format(org_id))
    return jsonify(item), 200
