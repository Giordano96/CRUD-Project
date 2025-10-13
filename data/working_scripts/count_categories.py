import pandas as pd

# Percorso del file CSV
file_path = "recipes.csv"

# Legge il file CSV
df = pd.read_csv(file_path)

# Conta le occorrenze di ogni categoria nella colonna
category_counts = df['cuisine_path'].value_counts()

# Mostra i risultati
print("Conteggio categorie nella colonna 'cuisine_path':")
print(category_counts)

