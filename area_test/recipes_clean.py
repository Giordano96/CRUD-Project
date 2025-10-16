import pandas as pd
import re

print("🍳 INIZIO PULIZIA E ESTRAZIONE PAROLE UNICHE")
print("=" * 50)

# ===============================
# 1. CARICA IL CSV
# ===============================
# Sostituisci con il percorso del tuo file
df = pd.read_csv('recipes.csv', on_bad_lines='skip')  # 'on_bad_lines' ignora righe malformate

print(f"✅ Caricato: {len(df)} ricette")
print("\nEsempio originale (prima riga):")
print(df['ingredients'].iloc[0][:200] + "...")  # Stampa i primi 200 char per verifica

# ===============================
# 2. ELIMINA PARENTESI E CONTENUTO
# ===============================
# Rimuove (contenuto) e parentesi residue, poi pulisce spazi extra
df['ingredients_clean'] = df['ingredients'].str.replace(r'\([^)]*\)', '', regex=True)  # Rimuove parentesi con contenuto
df['ingredients_clean'] = df['ingredients_clean'].str.replace(r'[\(\)]', '', regex=True)  # Rimuove parentesi singole
df['ingredients_clean'] = df['ingredients_clean'].str.strip()  # Rimuove spazi extra

print("\n✅ Parentesi eliminate")
print("\nEsempio pulito (prima riga):")
print(df['ingredients_clean'].iloc[0][:200] + "...")

# ===============================
# 3. ESTRAI PAROLE UNICHE
# ===============================
# Unisce tutti gli ingredienti puliti in una stringa
all_ingredients = ' '.join(df['ingredients_clean'].dropna())

# Divide in parole, rimuove punteggiatura e crea un set di parole uniche
words = re.split(r'[,\s]+', all_ingredients)  # Divide su virgole e spazi
words = [word.strip().lower() for word in words if word.strip()]  # Rimuove spazi e converte in minuscolo
unique_words = sorted(set(words))  # Rimuove duplicati e ordina alfabeticamente

print(f"\n✅ Estratte {len(unique_words)} parole uniche")

# ===============================
# 4. SALVA PAROLE UNICHE IN FILE TXT
# ===============================
with open('unique_ingredients.txt', 'w', encoding='utf-8') as f:
    f.write(','.join(unique_words))

print("\n💾 SALVATO: 'unique_ingredients.txt' (parole uniche separate da virgole)")
print(f"📊 Parole uniche totali: {len(unique_words)}")
print("\n👀 Prime 10 parole (anteprima):")
print(unique_words[:10])

# ===============================
# 5. SALVA CSV CON COLONNA PULITA
# ===============================
df.to_csv('recipes_senza_parentesi.csv', index=False)

print("\n💾 SALVATO: 'recipes_senza_parentesi.csv' (con colonna 'ingredients_clean')")
print("=" * 50)
print("✅ COMPLETATO! Controlla i file generati.")