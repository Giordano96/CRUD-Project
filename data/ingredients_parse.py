import pandas as pd
import re
from fractions import Fraction

# Mappa frazioni Unicode a numeri decimali
unicode_fraction_map = {
    '½': 0.5, '¼': 0.25, '¾': 0.75,
    '⅓': 1/3, '⅔': 2/3,
    '⅛': 0.125, '⅜': 0.375, '⅝': 0.625, '⅞': 0.875
}

# Lista di unità di misura valide, estesa
valid_units = [
    'cup', 'cups', 'tablespoon', 'tablespoons', 'teaspoon', 'teaspoons',
    'ounce', 'ounces', 'pound', 'pounds', 'gram', 'grams', 'kilogram', 'kilograms',
    'milliliter', 'milliliters', 'liter', 'liters', 'pinch', 'pinches',
    'can', 'cans', 'package', 'packages', 'bottle', 'bottles', 'jar', 'jars',
    'quart', 'quarts', 'inch', 'inches', 'fluid ounce', 'fluid ounces',
    'dash', 'batch', 'piece', 'pieces'
]

def parse_quantity(qty_str):
    qty_str = qty_str.strip()
    # Converti frazione Unicode
    for uf, val in unicode_fraction_map.items():
        if uf in qty_str:
            qty_str = qty_str.replace(uf, str(val))
    # Converti frazione mista tipo "1 1/2" o frazione semplice "1/2"
    if re.match(r'^\d+\s+\d+/\d+$', qty_str):
        whole, frac = qty_str.split()
        return round(float(whole) + float(Fraction(frac)), 2)
    elif re.match(r'^\d+/\d+$', qty_str):
        return round(float(Fraction(qty_str)), 2)
    else:
        try:
            return round(float(qty_str), 2)
        except:
            return None

def extract_ingredient(ingredient_str):
    ingredient_str = ingredient_str.strip()
    # Normalizza frazioni Unicode
    for uf, val in unicode_fraction_map.items():
        ingredient_str = ingredient_str.replace(uf, str(val))
    # Normalizza frazioni miste (es. "2 0.5" -> "2.5")
    if re.search(r'(\d+)\s+(\d+\.\d+|\d+/\d+)', ingredient_str):
        ingredient_str = re.sub(
            r'(\d+)\s+(\d+\.\d+|\d+/\d+)',
            lambda m: str(float(m.group(1)) + parse_quantity(m.group(2))),
            ingredient_str
        )
    # Regex per quantità, descrizione opzionale (in parentesi o dopo trattino), unità e ingrediente
    match = re.match(
        r'^\s*(\d+\.\d+|\d+)?(?:\s*\((.*?)\))?(?:\s*-?\s*(.*?))?\s*([a-zA-Z]+)?\s*(.*)',
        ingredient_str
    )
    if match:
        qty_raw = match.group(1)
        paren_desc = match.group(2) or ''
        dash_desc = match.group(3) or ''
        unit = match.group(4)
        ingredient = match.group(5).strip()
        quantity = parse_quantity(qty_raw) if qty_raw else None
        # Combina descrizioni da parentesi e trattino
        description = paren_desc
        if dash_desc:
            description = f"{paren_desc} {dash_desc}".strip() if paren_desc else dash_desc
        # Se l'unità non è valida, spostala nell'ingrediente e usa "piece"
        if unit and unit.lower() not in valid_units:
            ingredient = f"{unit} {ingredient}".strip()
            unit = 'piece'
        # Gestisci "(Optional)" e altre descrizioni
        if 'Optional' in description:
            description = 'Optional'
            ingredient = ingredient.replace('(Optional)', '').strip()
        # Evita che descrizioni come "skinless" diventino ingredienti
        if ingredient in ['skinless', 'boneless', '/2 inch thick']:
            return None  # Ignora ingredienti non validi
        return {
            'quantity': quantity,
            'unit': unit,
            'ingredient': ingredient,

        }
    else:
        return {'quantity': None, 'unit': None, 'ingredient': ingredient_str}

def split_ingredients(ingredients_str):
    # Dividi gli ingredienti evitando virgole all'interno di parentesi
    ingredients = []
    current = ''
    paren_count = 0
    for char in ingredients_str:
        if char == '(':
            paren_count += 1
        elif char == ')':
            paren_count -= 1
        elif char == ',' and paren_count == 0:
            ingredients.append(current.strip())
            current = ''
            continue
        current += char
    if current.strip():
        ingredients.append(current.strip())
    return ingredients

# Carica dataset completo
df = pd.read_csv("recipes.csv")
df = df.drop(columns=['Unnamed: 0'], errors='ignore')
df = df.dropna(subset=['ingredients'])

# Dividi gli ingredienti sulla virgola, gestendo parentesi
df['ingredients_list'] = df['ingredients'].apply(split_ingredients)

# Crea una nuova colonna 'ingredients_parsed' con gli ingredienti parsati
df['ingredients_parsed'] = df['ingredients_list'].apply(
    lambda ingredients: [ing for ing in [extract_ingredient(ing) for ing in ingredients] if ing and ing['quantity'] is not None]
)

# Stampa il DataFrame con la nuova colonna (solo alcune colonne per chiarezza)
print(df[['recipe_name', 'ingredients', 'ingredients_parsed']])

# Salva il DataFrame aggiornato in un nuovo file CSV
df.to_csv("recipes_with_parsed_ingredients.csv", index=False)
print("\nDataFrame salvato in 'recipes_with_parsed_ingredients.csv'")