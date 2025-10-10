import pandas as pd

# La tua lista di dizionari
df = pd.read_csv("recipes_with_parsed_ingredients.csv")
# Creazione del DataFrame


# Rimuovi parentesi graffe, quadre e punti interrogativi da tutte le celle (se presenti)
df = df.replace({r'[\[\]\{\}\'\,]': ''}, regex=True)

# Salva in un file CSV
df.to_csv('ricetta.csv', index=False)

print("File CSV salvato senza parentesi o punti interrogativi.")
