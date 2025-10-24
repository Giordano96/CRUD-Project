import pandas as pd
import re

# Carica il file CSV pulito
df = pd.read_csv('ingredients_with_tags.csv')

# 🔹 Rimuovi righe dove 'ingredients_parsed' è nullo o contiene "inch", "quart", "quarts"
df = df.dropna(subset=['ingredients_parsed'])
df = df[~df['ingredients_parsed'].str.contains(r'\b(inch|quart|quarts)\b', case=False, na=False)]

# Mappatura delle conversioni fornita
conversion_map = {
    "tablespoon": (15, 'g'),
    "tablespoons": (15, 'g'),
    "teaspoon": (5, 'g'),
    "teaspoons": (5, 'g'),
    "pound": (450, 'g'),
    "pounds": (450, 'g'),
    "pinch": (1, 'g'),
    "pinches": (1, 'g'),
    "dash": (1, 'g'),
    "ounce": (30, 'g'),
    "ounces": (30, 'g'),
    "cup": {
        "default": (240, 'ml'),
        "water": (240, 'ml'),
        "milk": (240, 'ml'),
        "cream": (240, 'ml'),
        "sugar": (210, 'g'),
        "flour": (128, 'g'),
        "whole wheat flour": (130, 'g'),
        "butter": (228, 'g'),
        "rice": (200, 'g'),
        "cocoa": (88, 'g')
    },
    "cups": {
        "default": (240, 'ml'),
        "water": (240, 'ml'),
        "milk": (240, 'ml'),
        "cream": (240, 'ml'),
        "sugar": (210, 'g'),
        "flour": (128, 'g'),
        "whole wheat flour": (130, 'g'),
        "butter": (228, 'g'),
        "rice": (200, 'g'),
        "cocoa": (88, 'g')
    }
}

# Elenco delle unità metriche da non convertire
metric_units = {'g', 'ml', 'kg', 'l', 'mg'}

# Funzione per ottenere il fattore di conversione per 'cup' o 'cups'
def get_cup_conversion(ingredient):
    ingredient = ingredient.lower().strip()
    for key in conversion_map["cup"]:
        if key in ingredient:
            return conversion_map["cup"][key]
    return conversion_map["cup"]["default"]

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

    # Non convertire unità già metriche
    if unit_lower in metric_units:
        return entry

    if unit_lower in conversion_map:
        if unit_lower in ["cup", "cups"]:
            factor, new_unit = get_cup_conversion(ingredient)
        else:
            factor, new_unit = conversion_map[unit_lower]
        new_quantity = round(quantity * factor)
        return f'quantity: {new_quantity} unit: {new_unit} ingredient: {ingredient}'
    else:
        # Unità non convertibile
        return entry

# Funzione per convertire tutti gli ingredienti in una riga
def convert_ingredients_parsed(row):
    if pd.isna(row):
        return row

    ingredients = row.split(' quantity: ')[1:]
    ingredients = ['quantity: ' + ingredient for ingredient in ingredients]

    converted_ingredients = [convert_entry(ingredient) for ingredient in ingredients]

    return ' '.join(converted_ingredients)

# Applica la funzione alla colonna ingredients_parsed
df['ingredients_parsed'] = df['ingredients_parsed'].apply(convert_ingredients_parsed)

# Visualizza il risultato
print(df[['recipe_name', 'ingredients_parsed']].head(10))

# Salva il DataFrame convertito in un nuovo file CSV
df.to_csv('recipes_to_metric.csv', index=False)

print("✅ File salvato come: recipes_to_metric.csv")
