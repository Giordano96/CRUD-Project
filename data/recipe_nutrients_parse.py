# Importa la libreria pandas per la gestione dei file CSV
import pandas as pd

# Importa il modulo re per l'uso di espressioni regolari
import re

# Lista dei macronutrienti da estrarre (in grammi)
macronutrients = [
    "Total Fat",
    "Saturated Fat",
    "Total Carbohydrate",
    "Dietary Fiber",
    "Total Sugars",
    "Protein"
]


# Funzione per analizzare la stringa della colonna nutrition ed estrarre i macronutrienti in grammi
def parse_nutrition(nutrition_str):
    # Inizializza un dizionario per i macronutrienti con valore predefinito 0
    macro_dict = {macro: 0 for macro in macronutrients}

    # Gestisce stringhe di nutrition mancanti o non valide
    if not isinstance(nutrition_str, str):
        return ", ".join([f"{macro}: {value}g" for macro, value in macro_dict.items()])

    # Pattern regex per identificare il nome del nutriente e il valore in grammi (es. "Total Fat 18g")
    pattern = r"(\w+(?:\s+\w+)*)\s+(\d+\.?\d*)g"

    # Trova tutte le corrispondenze del pattern nella stringa di nutrition
    matches = re.findall(pattern, nutrition_str)

    # Elabora ogni corrispondenza trovata
    for nutrient, value in matches:
        # Include solo i macronutrienti specificati e converte il valore in intero
        if nutrient in macronutrients:
            macro_dict[nutrient] = (float(value))  # Converte in intero per uniformità

    # Formatte l'output come stringa (es. "Total Fat: 18, Saturated Fat: 7, ...")
    return ", ".join([f"{macro}: {value}" for macro, value in macro_dict.items()])


# Legge il file CSV recipes.csv in un DataFrame
df = pd.read_csv("recipes.csv")

# Applica la funzione parse_nutrition alla colonna nutrition
df['nutrition'] = df['nutrition'].apply(parse_nutrition)

# Sovrascrive il file CSV originale con i dati aggiornati
df.to_csv("recipes.csv", index=False)

# Stampa un messaggio di conferma dell'aggiornamento del file CSV
print("Aggiornato 'recipes.csv' con la colonna nutrition normalizzata")