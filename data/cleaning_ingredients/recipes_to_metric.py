# recipes_to_metric_FIXED.py
import pandas as pd
import re

df = pd.read_csv('ingredients_with_tags.csv')


def convert_ingredients_safely(text):
    if pd.isna(text):
        return text

    # Trova TUTTI i blocchi "quantity: ... unit: ... ingredient: ..."
    pattern = r'quantity:\s*([\d.]+)\s+unit:\s*(\S+)\s+ingredient:\s*(.+?)(?=quantity:|$)'
    matches = re.finditer(pattern, text)

    new_parts = []
    last_end = 0

    for match in matches:
        qty, unit, ing = match.groups()
        qty = float(qty)
        unit = unit.lower()

        # Mantieni tutto il testo prima del match
        new_parts.append(text[last_end:match.start()])

        # Converti solo se possibile
        if unit in ['cup', 'cups']:
            ing_lower = ing.lower()
            if 'flour' in ing_lower:
                new_qty = round(qty * 128)
                new_unit = 'g'
            elif 'sugar' in ing_lower:
                new_qty = round(qty * 200)
                new_unit = 'g'
            elif 'butter' in ing_lower:
                new_qty = round(qty * 227)
                new_unit = 'g'
            else:
                new_qty = round(qty * 240)
                new_unit = 'ml'
        elif unit in ['tablespoon', 'tablespoons', 'tbsp']:
            new_qty = round(qty * 15)
            new_unit = 'g' if any(x in ing.lower() for x in ['flour', 'sugar', 'butter']) else 'ml'
        elif unit in ['teaspoon', 'teaspoons', 'tsp']:
            new_qty = round(qty * 5)
            new_unit = 'g' if any(x in ing.lower() for x in ['salt', 'baking']) else 'ml'
        elif unit in ['pound', 'pounds', 'lb']:
            new_qty = round(qty * 450)
            new_unit = 'g'
        elif unit in ['ounce', 'ounces', 'oz']:
            new_qty = round(qty * 28)
            new_unit = 'g'
        else:
            new_qty = qty
            new_unit = unit

        new_parts.append(f"quantity: {new_qty} unit: {new_unit} ingredient: {ing}")
        last_end = match.end()

    # Aggiungi il testo finale (dopo l'ultimo match)
    new_parts.append(text[last_end:])

    return ''.join(new_parts)


# Applica in modo sicuro
print("Conversione in corso (senza perdere ingredienti)...")
df['ingredients_parsed'] = df['ingredients_parsed'].apply(convert_ingredients_safely)

# Salva
df.to_csv('recipes_cleaned.csv', index=False)
print("Fatto! File salvato come recipes_cleaned.csv")
print("   → Nessun ingrediente è stato eliminato")