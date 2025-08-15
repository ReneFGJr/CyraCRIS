import sys
import capes_import_orgunit

def header():
    print("CyraCRIS Tools    v0.25.08.15")
    print("=============================")

if __name__ == "__main__":
    args = sys.argv[1:]  # Captura argumentos da linha de comando

    header()

    if (len(args) < 1):
        print("use: python cyraCRIS.py VERB PARM1 PARM2 ....")
        sys.exit(1)

    if (args[0] == "orgunit"):
        file_path = "data/terceiros/br-capes-colsucup-prog-2023-2025-03-31.csv"
        capes_import_orgunit.import_orgunit(file_path)

    else:
        print("Unknown command:", args[0])
        sys.exit(1)
