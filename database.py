import mysql.connector
import env

def execute_query(qr, fetch=False):
    """Executa uma query SQL no banco MySQL."""
    resultados = []
    try:
        config = env.db()
        # Força charset UTF-8
        config.update({"charset": "utf8mb4", "use_unicode": True})

        with mysql.connector.connect(**config) as conexao:
            with conexao.cursor() as cursor:
                cursor.execute(qr)
                if fetch:
                    resultados = cursor.fetchall()
                else:
                    conexao.commit()
                cursor.close()
                conexao.close()

    except mysql.connector.Error as erro:
        print(f"Erro de Banco de Dados: {erro}")
        print(f"Query: {qr}")

    return resultados


def query(qr):
    """Executa SELECT e retorna resultados."""
    return execute_query(qr, fetch=True)


def insert(qr):
    """Executa INSERT."""
    execute_query(qr, fetch=False)


def update(qr):
    """Executa UPDATE."""
    execute_query(qr, fetch=False)
