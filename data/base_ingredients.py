import pandas as pd
import re
from collections import Counter

# Mappatura statica degli ingredienti base
base_ingredients_map = {
    'butter': 'butter',
    'unsalted butter': 'butter',
    'salted butter': 'butter',
    'apples': 'apple',
    'granny smith apples': 'apple',
    'macintosh apples': 'apple',
    'sugar': 'sugar',
    'white sugar': 'sugar',
    'brown sugar': 'sugar',
    'granulated sugar': 'sugar',
    'powdered sugar': 'sugar',
    'confectioners\' sugar': 'sugar',
    'cranberries': 'cranberries',
    'fresh cranberries': 'cranberries',
    'dried cranberries': 'cranberries',
    'pastry': 'pastry',
    'pie crust': 'pastry',
    'puff pastry': 'pastry',
    'frozen puff pastry': 'pastry',
    'doublecrust pie pastry': 'pastry',
    'egg': 'egg',
    'eggs': 'egg',
    'egg white': 'egg',
    'cream': 'cream',
    'heavy cream': 'cream',
    'whipping cream': 'cream',
    'whipped cream': 'cream',
    'ice cream': 'cream',
    'flour': 'flour',
    'all-purpose flour': 'flour',
    'whole wheat flour': 'flour',
    'water': 'water',
    'cinnamon': 'cinnamon',
    'ground cinnamon': 'cinnamon',
    'salt': 'salt',
    'kosher salt': 'salt',
    'oil': 'oil',
    'olive oil': 'oil',
    'vegetable oil': 'oil',
    'milk': 'milk',
    'whole milk': 'milk',
    'chicken': 'chicken',
    'chicken breast': 'chicken',
    'chicken thighs': 'chicken',
    'cheese': 'cheese',
    'cheddar cheese': 'cheese',
    'parmesan cheese': 'cheese',
    'vanilla': 'vanilla',
    'vanilla extract': 'vanilla',
    'lemon': 'lemon',
    'lemon juice': 'lemon',
    'onion': 'onion',
    'garlic': 'garlic',
    'pepper': 'pepper',
    'black pepper': 'pepper',
    'bell pepper': 'pepper',
    'tomato': 'tomato',
    'tomatoes': 'tomato',
    'potato': 'potato',
    'potatoes': 'potato',
    'carrot': 'carrot',
    'carrots': 'carrot',
    'honey': 'honey',
    'vinegar': 'vinegar',
    'balsamic vinegar': 'vinegar',
    'baking powder': 'baking powder',
    'baking soda': 'baking soda',
    'chili powder': 'chili powder',
    'prunes': 'prune',
    'shrimp': 'shrimp',
    'medium shrimp': 'shrimp',
    'spinach': 'spinach',
    'frozen chopped spinach': 'spinach',
    'red lentils': 'lentil',
    'chuck roast': 'beef',
    'papaya': 'papaya',
    'peach': 'peach',
    'fresh peach': 'peach',
    'thyme': 'thyme',
    'fresh thyme': 'thyme',
    'fresh thyme leaves': 'thyme',
    'cumin': 'cumin',
    'basil': 'basil',
    'basil leaves': 'basil',
    'fruit cocktail': 'fruit cocktail',
    'gin': 'gin',
    'apricot': 'apricot',
    'fresh apricot': 'apricot',
    'bread crumbs': 'bread crumbs',
    'seasoned bread crumbs': 'bread crumbs',
    'jello': 'jello',
    'cherry jell-o': 'jello',
    'jicama': 'jicama',
    'cabbage': 'cabbage',
    'cherries': 'cherry',
    'fresh cherries': 'cherry',
    'mozzarella': 'mozzarella',
    'fresh mozzarella': 'mozzarella',
    'pork ribs': 'pork',
    'countrystyle pork ribs': 'pork',
    'figs': 'fig',
    'canned figs': 'fig',
    'prickly pears': 'prickly pear',
    'harissa': 'harissa',
    'berries': 'berry',
    'seasonal berries': 'berry',
    'grapes': 'grape',
    'green grapes': 'grape',
    'tomatillos': 'tomatillo',
    'fresh tomatillos': 'tomatillo',
    'oats': 'oats',
    'quick cooking oats': 'oats',
    'chocolate chips': 'chocolate chip',
    'mango': 'mango',
    'minced mango': 'mango',
    'almonds': 'almond',
}


def generate_dynamic_mapping(all_ingredients, min_freq=1):
    """Genera una mappatura dinamica per ingredienti frequenti."""
    dynamic_map = {}
    ingredient_counts = Counter(all_ingredients)
    common_ingredients = [ing for ing, count in ingredient_counts.items() if count >= min_freq]

    for ingredient in common_ingredients:
        # Pulisci l'ingrediente senza introdurre spazi tra le lettere
        cleaned = re.sub(
            r'\b(cored|sliced|peeled|chopped|diced|minced|grated|beaten|whipped|fresh|dried|packed|medium|small|large|ground|crushed|pureed|halved|quartered|finely|coarsely|melted|softened|room temperature|to taste|for dusting|and|or|as needed|lightly|thawed|optional|other|firm|soft-textured|that fall apart when cooked|frozen|sheet|package|still cold|white|sweetened|into|rings|inch|thick|country\s*style|seasoned|but|ice|allpurpose|quick\s*cooking|crisp|doublecrust|cup|tablespoon|teaspoon|ounce|oz|pound|lb)\b|[½¼¾⅓⅔⅛⅜⅝⅞]|\d+/\d+|\d*\.?\d*\s*(?:tablespoons?|teaspoons?|cups?|ounces?|oz|pounds?|lb|inches?|inch)?|\([^)]*\)|-|:|;|,',
            '', ingredient).strip()
        # Normalizza spazi multipli
        cleaned = ' '.join(cleaned.split()).strip()
        if cleaned and cleaned not in base_ingredients_map.values() and cleaned not in dynamic_map.values():
            # Correggi termini concatenati
            cleaned = (cleaned.replace('brownsugar', 'brown sugar')
                       .replace('grannysmithapples', 'apple')
                       .replace('macintoshapples', 'apple')
                       .replace('puffpastry', 'pastry')
                       .replace('creamcream', 'cream')
                       .replace('doublecrustpiepastry', 'pastry')
                       .replace('unsaltedbutter', 'butter')
                       .replace('allpurposeflour', 'flour'))
            dynamic_map[ingredient] = cleaned

    return dynamic_map


def normalize_ingredient_name(ingredient_str):
    """Normalizza il nome dell'ingrediente, mappandolo al nome base."""
    ingredient_str = ingredient_str.lower().strip()
    for base_name, normalized_name in base_ingredients_map.items():
        if base_name in ingredient_str:
            return normalized_name
    # Rimuovi descrizioni, frazioni, quantità, unità, parentesi e caratteri speciali
    cleaned = re.sub(
        r'\b(cored|sliced|peeled|chopped|diced|minced|grated|beaten|whipped|fresh|dried|packed|medium|small|large|ground|crushed|pureed|halved|quartered|finely|coarsely|melted|softened|room temperature|to taste|for dusting|and|or|as needed|lightly|thawed|optional|other|firm|soft-textured|that fall apart when cooked|frozen|sheet|package|still cold|white|sweetened|into|rings|inch|thick|country\s*style|seasoned|but|ice|allpurpose|quick\s*cooking|crisp|doublecrust|cup|tablespoon|teaspoon|ounce|oz|pound|lb)\b|[½¼¾⅓⅔⅛⅜⅝⅞]|\d+/\d+|\d*\.?\d*\s*(?:tablespoons?|teaspoons?|cups?|ounces?|oz|pounds?|lb|inches?|inch)?|\([^)]*\)|-|:|;|,',
        '', ingredient_str).strip()
    # Normalizza spazi multipli
    cleaned = ' '.join(cleaned.split()).strip()
    # Correggi termini concatenati
    cleaned = (cleaned.replace('brownsugar', 'brown sugar')
               .replace('grannysmithapples', 'apple')
               .replace('macintoshapples', 'apple')
               .replace('puffpastry', 'pastry')
               .replace('creamcream', 'cream')
               .replace('doublecrustpiepastry', 'pastry')
               .replace('unsaltedbutter', 'butter')
               .replace('allpurposeflour', 'flour'))
    return cleaned if cleaned else None


def parse_ingredients(ingredients_str):
    """Estrae e normalizza gli ingredienti, restituendo una stringa separata da virgole."""
    if not isinstance(ingredients_str, str) or not ingredients_str.strip():
        return ""

    ingredients = [i.strip() for i in ingredients_str.split(',')]
    normalized = []
    # Regex aggiornata per rimuovere frazioni, quantità, unità, parentesi, trattini e descrizioni
    quantity_pattern = r'^\d*\.?\d*\s*(?:tablespoons?|teaspoons?|cups?|ounces?|oz|pounds?|lb|inches?|inch|medium|small|large)?\s*|[½¼¾⅓⅔⅛⅜⅝⅞]|\d+/\d+\s*|\([^)]*\)|-|\b(and|or|as needed|lightly|thawed|optional|firm|soft-textured|still cold|white|sweetened|into|rings|inch|thick|country\s*style|seasoned|but|ice|allpurpose|quick\s*cooking|crisp|doublecrust|cup|tablespoon|teaspoon|ounce|oz|pound|lb)\b|:|;|,'

    for ingredient in ingredients:
        cleaned = re.sub(quantity_pattern, '', ingredient, flags=re.IGNORECASE).strip()
        cleaned = ' '.join(cleaned.split()).strip()
        normalized_name = normalize_ingredient_name(cleaned)
        if normalized_name and normalized_name not in normalized:
            normalized.append(normalized_name)

    return ", ".join(normalized)


def extract_all_ingredients(df):
    """Estrae tutti gli ingredienti unici dal dataset."""
    all_ingredients = []
    quantity_pattern = r'^\d*\.?\d*\s*(?:tablespoons?|teaspoons?|cups?|ounces?|oz|pounds?|lb|inches?|inch|medium|small|large)?\s*|[½¼¾⅓⅔⅛⅜⅝⅞]|\d+/\d+\s*|\([^)]*\)|-|\b(and|or|as needed|lightly|thawed|optional|firm|soft-textured|still cold|white|sweetened|into|rings|inch|thick|country\s*style|seasoned|but|ice|allpurpose|quick\s*cooking|crisp|doublecrust|cup|tablespoon|teaspoon|ounce|oz|pound|lb)\b|:|;|,'

    for ingredients_str in df['ingredients']:
        if not isinstance(ingredients_str, str):
            continue
        ingredients = [i.strip() for i in ingredients_str.split(',')]
        for ingredient in ingredients:
            cleaned = re.sub(quantity_pattern, '', ingredient, flags=re.IGNORECASE).strip()
            cleaned = ' '.join(cleaned.split()).strip()
            if cleaned:
                all_ingredients.append(cleaned.lower())

    return all_ingredients


def check_mapping_coverage(all_ingredients):
    """Verifica quali ingredienti non sono coperti dalla mappatura."""
    unmapped_ingredients = []
    for ingredient in set(all_ingredients):
        normalized = normalize_ingredient_name(ingredient)
        if normalized and normalized not in base_ingredients_map.values():
            if not any(base_name in ingredient for base_name in base_ingredients_map):
                unmapped_ingredients.append(ingredient)

    return unmapped_ingredients


# Leggi l'intero dataset
df = pd.read_csv("recipes.csv")

# Estrai tutti gli ingredienti unici
all_ingredients = extract_all_ingredients(df)

# Genera una mappatura dinamica per ingredienti frequenti
dynamic_map = generate_dynamic_mapping(all_ingredients, min_freq=1)
base_ingredients_map.update(dynamic_map)

# Verifica la copertura della mappatura aggiornata
unmapped_ingredients = check_mapping_coverage(all_ingredients)

# Crea la colonna base_ingredients
df['base_ingredients'] = df['ingredients'].apply(parse_ingredients)

# Salva il DataFrame in un nuovo file CSV
df.to_csv("recipes_with_base_ingredients.csv", index=False)

# Stampa statistiche
print(f"Totale ricette processate: {len(df)}")
print(f"Ricette con base_ingredients vuota: {len(df[df['base_ingredients'] == ''])}")
print(f"Totale ingredienti unici trovati: {len(set(all_ingredients))}")
print(f"Ingredienti non coperti dalla mappatura aggiornata: {len(unmapped_ingredients)}")
if unmapped_ingredients:
    print("Esempi di ingredienti non coperti (primi 10):")
    print(unmapped_ingredients[:10])
else:
    print("Tutti gli ingredienti sono coperti dalla mappatura aggiornata.")

# Stampa le prime tre righe per verifica
print("\nPrime tre righe di 'base_ingredients':")
for index, row in df.head(3).iterrows():
    print(f"Ricetta: {row['recipe_name']}")
    print(f"base_ingredients: {row['base_ingredients']}")

# Stampa i primi 10 ingredienti della mappatura dinamica
print("\nPrimi 10 ingredienti della mappatura dinamica aggiunti:")
for k, v in list(dynamic_map.items())[:10]:
    print(f"{k} -> {v}")