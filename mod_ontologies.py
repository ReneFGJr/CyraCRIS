from rdflib import Graph, RDF, RDFS, OWL, URIRef
from rdflib.namespace import split_uri
import os, sys


def gerar_inserts_rdf_class(owl_path: str, out_sql_path: str) -> None:
    """
    Lê uma ontologia OWL/RDF e gera INSERTs para a tabela rdf_class.
    Mapeamento:
      - c_class: local name (até 200)
      - c_url: namespace/base IRI (até 100)
      - c_type: 'C' p/ classes, 'P' p/ propriedades
      - c_description: rdfs:label ou rdfs:comment (se existir)
    """
    if not os.path.exists(owl_path):
        print(f"❌ Arquivo não encontrado: {file}")
        return None

    g = Graph()
    g.parse(owl_path)  # autodetecta formato

    def localname(uri: URIRef) -> str:
        s = str(uri)
        try:
            return split_uri(uri)[1]
        except Exception:
            if "#" in s:
                return s.rsplit("#", 1)[-1]
            return s.rstrip("/").rsplit("/", 1)[-1]

    def namespace_of(uri: URIRef) -> str:
        s = str(uri)
        if "#" in s:
            return s.rsplit("#", 1)[0] + "#"
        return s.rsplit("/", 1)[0] + "/"

    def first_text(s, preds):
        for p in preds:
            for o in g.objects(s, p):
                if isinstance(o, URIRef):
                    continue
                txt = str(o).strip()
                if txt:
                    return txt
        return ""

    def sql_escape(v: str) -> str:
        return v.replace("\\", "\\\\").replace("'", "\\'").replace("\x00", "")

    rows = []

    # Classes
    for s in set(g.subjects(RDF.type, OWL.Class)):
        if isinstance(s, URIRef):
            name = localname(s)[:200]
            ns = namespace_of(s)[:100]

            desc = first_text(s, [RDFS.label, RDFS.comment])
            rows.append(("C", name, ns, desc))

    # Propriedades
    seen = set()
    for ptype in (OWL.ObjectProperty, OWL.DatatypeProperty, RDF.Property):
        for s in set(g.subjects(RDF.type, ptype)):
            if isinstance(s, URIRef):
                name = localname(s)[:200]
                ns = namespace_of(s)[:100]
                key = (name, ns)
                if key in seen:
                    continue
                seen.add(key)
                desc = first_text(s, [RDFS.label, RDFS.comment])
                rows.append(("P", name, ns, desc))

    # Dedup e grava SQL
    seen_full = set()
    with open(out_sql_path, "w", encoding="utf-8") as f:
        for typ, name, ns, desc in rows:
            key = (typ, name, ns)
            if key in seen_full:
                continue
            seen_full.add(key)
            stmt = (
                "INSERT INTO rdf_class "
                "(c_class_main, c_class, c_equivalent, c_prefix, c_type, c_description, c_url) "
                f"VALUES ('{sql_escape(name)}', '{sql_escape(desc)}', 0, 0, '{typ}', '', '{sql_escape(ns)}/{sql_escape(name)}');\n"
            )
            f.write(stmt)


file = "_documments/cyraCRIS.owl"
if not os.path.exists(file):
    print(f"❌ Arquivo não encontrado: {file}")
    sys.exit(1)
gerar_inserts_rdf_class(file, "_documments/data_classes_propriedades.sql")
