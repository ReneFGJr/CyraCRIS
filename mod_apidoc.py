import re
from pathlib import Path
from html import escape


def gerar_documentacao_api(arquivo_py: str) -> str:
    """
    Lê o arquivo Flask informado e gera um HTML com todos os endpoints,
    parâmetros, e metadados das anotações (# @descrition e # @return).
    """

    texto = Path(arquivo_py).read_text(encoding="utf-8")

    # Captura os endpoints e as anotações anteriores
    padrao = re.compile(
        r'(@app\.(get|post|put|delete)\("([^"]+)"\))'  # método + rota
        r'(?:\s*#\s*@descrition:\s*(?P<desc>.*?)\n)?'  # descrição
        r'(?:\s*#\s*@return:\s*(?P<ret>.*?)\n)?'  # retorno
        r'\s*def\s+(?P<func>\w+)\((?P<params>.*?)\):',  # definição da função
        re.DOTALL | re.IGNORECASE)

    rotas = []
    for match in padrao.finditer(texto):
        metodo = match.group(2).upper()
        rota = match.group(3)
        funcao = match.group("func")
        params = match.group("params").strip()
        descricao = (match.group("desc") or "").strip()
        retorno = (match.group("ret") or "").strip()

        # bloco de código da função (para extrair variáveis)
        inicio_func = match.end()
        proximo_def = texto.find("@app", inicio_func)
        if proximo_def == -1:
            bloco = texto[inicio_func:]
        else:
            bloco = texto[inicio_func:proximo_def]

        entradas = []

        # Parâmetros de rota: <org_id>
        for v in re.findall(r"<(\w+)>", rota):
            entradas.append(f"URL param: {v}")

        # Query params: request.args.get("...")
        for v in re.findall(r'request\.args\.get\(["\'](\w+)["\']', bloco):
            entradas.append(f"Query param: {v}")

        # Form params: request.form.get("...") ou getlist("...")
        for v in re.findall(r'request\.form\.get(?:list)?\(["\'](\w+)["\']',
                            bloco):
            entradas.append(f"Form param: {v}")

        rotas.append({
            "metodo": metodo,
            "rota": rota,
            "params": params,
            "descricao": descricao,
            "retorno": retorno,
            "entradas": sorted(set(entradas))
        })

    # --- Gera o HTML ---
    html = [
        "<html><head><meta charset='utf-8'><title>Documentação da API</title>",
        "<style>",
        "body{font-family:Arial, sans-serif;margin:2em;background:#fafafa;}",
        "h1{color:#333;margin-bottom:1em;}",
        "table{border-collapse:collapse;width:100%;background:#fff;box-shadow:0 2px 5px #ccc;}",
        "th,td{border:1px solid #ddd;padding:8px;vertical-align:top;}",
        "th{background:#eee;}", "tr:hover{background:#f9f9f9;}",
        "code{background:#f2f2f2;padding:2px 4px;border-radius:4px;}",
        "</style></head><body>", "<h1>📘 Documentação Automática da API</h1>",
        "<table>",
        "<tr><th>Método</th><th>Endpoint</th><th>Descrição</th>"
        "<th>Parâmetros</th><th>Entradas</th><th>Retorno</th></tr>"
    ]

    for r in rotas:
        html.append(f"<tr>"
                    f"<td>{r['metodo']}</td>"
                    f"<td><code>{escape(r['rota'])}</code></td>"
                    f"<td>{escape(r['descricao'] or '-')}</td>"
                    f"<td>{escape(r['params'] or '-')}</td>"
                    f"<td>{'<br>'.join(r['entradas']) or '-'}</td>"
                    f"<td>{escape(r['retorno'] or '-')}</td>"
                    f"</tr>")

    html.append("</table></body></html>")
    return "\n".join(html)
