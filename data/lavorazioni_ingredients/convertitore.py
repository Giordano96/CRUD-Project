import pandas as pd
import re

# Carica il file CSV pulito
df = pd.read_csv('ricetta_cleaned_2.csv')

# Dizionario di conversioni da unità imperiali a metriche
# Volumi: in ml o g (per tbsp/tsp usiamo g come richiesto, approx 15g per tbsp, 5g per tsp)
# Pesi: in g
conversion_factors = {
    'cup': (240, 'ml'),
    'cups': (240, 'ml'),# 1 cup = 240 ml
    'tablespoon': (15, 'g'),
    'tablespoons': (15, 'g'),# 1 tbsp ≈ 15 g (come suggerito, per semplicità)
    'teaspoon': (5, 'g'),
    'teaspoons': (5, 'g'),# 1 tsp ≈ 5 g
    'pint': (475, 'ml'),
    'pints': (475, 'ml'),# 1 pint = 473 ml
    'fluid': (30, 'ml'),  # 1 fluid ounce ≈ 30 ml (assumendo 'fluid' si riferisca a fluid ounce)
    'ounce': (30, 'g'),
    'ounces': (30, 'g'),# 1 ounce = 28 g (per pesi solidi; nota: per liquidi sarebbe 30 ml, ma qui assumiamo generico)
    'pound': (455, 'g'),
    'pounds': (455, 'g'),# 1 pound = 454 g
    # Aggiungi altre unità imperiali se necessario, es. 'gallon': (3785, 'ml'), 'quart': (946, 'ml')
    # Unità già metriche o non convertibili (es. 'piece', 'pinch', 'can') rimangono invariate
}


# Funzione per convertire una singola entry
def convert_entry(entry):
    if pd.isna(entry):
        return entry

    match = re.match(r'quantity: ([\d\.]+) unit: ([^\s]+) ingredient: (.+)', entry)
    if not match:
        return entry

    quantity_str, unit, ingredient = match.groups()
    quantity = float(quantity_str)
    unit_lower = unit.lower()

    if unit_lower in conversion_factors:
        factor, new_unit = conversion_factors[unit_lower]
        new_quantity = round(quantity * factor)  # Arrotonda a intero per leggibilità
        return f'quantity: {new_quantity} unit: {new_unit} ingredient: {ingredient}'
    else:
        # Unità già metrica o non convertibile, mantieni invariata
        return entry


# Funzione per convertire tutti gli ingredienti in una riga
def convert_ingredients_parsed(row):
    if pd.isna(row):
        return row

    # Dividi la stringa in una lista di ingredienti (separati da ' quantity: ')
    ingredients = row.split(' quantity: ')[1:]  # Salta il primo split vuoto
    ingredients = ['quantity: ' + ingredient for ingredient in ingredients]

    # Applica la conversione a ciascun ingrediente
    converted_ingredients = [convert_entry(ingredient) for ingredient in ingredients]

    # Ricostruisci la stringa unendo gli ingredienti convertiti
    return ' '.join(converted_ingredients)


# Applica la funzione alla colonna ingredients_parsed
df['ingredients_parsed'] = df['ingredients_parsed'].apply(convert_ingredients_parsed)

# Visualizza il risultato
print(df[['recipe_name', 'ingredients_parsed']].head(10))

# Salva il DataFrame convertito in un nuovo file CSV
df.to_csv('ricetta_metric.csv', index=False)