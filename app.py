# app.py
from flask import Flask, request, jsonify, abort

app = Flask(__name__)

DB = {}


@app.get("/")
def main():
    return jsonify(status=200,message="API is running")

@app.get("/status")
def status():
    return jsonify(status=201, message="Services OK")

@app.get("/orgunit")
def search():
    return jsonify(status=200, message="API is running",item=request.args)
