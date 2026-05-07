---
name: expense-categorization
description: Maps merchants and text patterns to LifeOS expense categories. Used by the email-ingestion agent (and later the bank-statement processor) to assign a deterministic category instead of inventing one.
when_to_use: Triggered whenever an agent or assistant must assign an `expenses.create` payload's `category` and `subcategory` fields. Skip when the user has explicitly chosen a category in the source data.
---

# Expense categorization

Agents categorize expenses by walking the rule list below top to bottom and stopping at the first match. Match-patterns are case-insensitive substrings tested against the **merchant** field first, then the email **subject + body**. Add or remove rules as your spending changes.

If nothing matches, the agent assigns `category: "uncategorized"` and never invents a new top-level category. Add a rule rather than letting the agent guess.

## Categories

The canonical category values are listed here. These are what end up in the `expenses.category` field. Subcategories are optional but useful for analytics.

- **groceries** — `supermarket`, `convenience`, `farmers-market`, `butcher`, `bakery`
- **dining** — `restaurant`, `delivery`, `coffee`, `bar`, `fast-food`
- **transport** — `rideshare`, `taxi`, `fuel`, `public-transit`, `parking`, `tolls`, `car-service`
- **utilities** — `electricity`, `water`, `internet`, `mobile`, `heating`, `gas`, `waste`
- **subscriptions** — `streaming`, `news`, `cloud`, `dev-tools`, `saas`, `gym`
- **shopping** — `clothing`, `electronics`, `home`, `books`, `appliances`, `furniture`
- **health** — `pharmacy`, `doctor`, `dentist`, `optical`, `lab`, `therapy`
- **entertainment** — `cinema`, `concert`, `gaming`, `event`, `museum`
- **travel** — `flight`, `hotel`, `rental-car`, `accommodation`, `train`
- **education** — `course`, `book`, `tuition`, `certification`
- **insurance** — `health`, `car`, `home`, `life`
- **banking** — `fee`, `interest`, `transfer`
- **pets** — `food`, `vet`, `supplies`
- **personal-care** — `haircut`, `cosmetics`, `spa`
- **gifts** — `gift`
- **charity** — `donation`
- **taxes** — `vat`, `income-tax`, `property-tax`
- **household** — `cleaning`, `repairs`, `furnishings`
- **uncategorized** — fallback only

## Rules

Add merchant-specific rules near the top so they win over generic substring matches. Anything not on this list will fall through to `uncategorized`.

```
# --- Groceries -------------------------------------------------------------
lidl                -> groceries / supermarket
konzum              -> groceries / supermarket
tinex               -> groceries / supermarket
kam                 -> groceries / supermarket
ramstore            -> groceries / supermarket
vero                -> groceries / supermarket
stokomak            -> groceries / supermarket

# --- Dining out ------------------------------------------------------------
glovo               -> dining / delivery
wolt                -> dining / delivery
mcdonald            -> dining / fast-food
kfc                 -> dining / fast-food
burger king         -> dining / fast-food
starbucks           -> dining / coffee
costa coffee        -> dining / coffee

# --- Transport -------------------------------------------------------------
bolt                -> transport / rideshare
uber                -> transport / rideshare
yandex              -> transport / rideshare
makpetrol           -> transport / fuel
okta                -> transport / fuel
shell               -> transport / fuel
omv                 -> transport / fuel
lukoil              -> transport / fuel
parking             -> transport / parking
jsp                 -> transport / public-transit

# --- Utilities -------------------------------------------------------------
evn                 -> utilities / electricity
makedonski telekom  -> utilities / internet
telekom             -> utilities / internet
a1                  -> utilities / mobile
toplifikacija       -> utilities / heating
balkan energy       -> utilities / heating
vodovod             -> utilities / water

# --- Subscriptions / SaaS -------------------------------------------------
netflix             -> subscriptions / streaming
spotify             -> subscriptions / streaming
youtube premium     -> subscriptions / streaming
youtube music       -> subscriptions / streaming
disney              -> subscriptions / streaming
hbo                 -> subscriptions / streaming
apple.com/bill      -> subscriptions / cloud
icloud              -> subscriptions / cloud
google one          -> subscriptions / cloud
google storage      -> subscriptions / cloud
dropbox             -> subscriptions / cloud
github              -> subscriptions / dev-tools
openai              -> subscriptions / dev-tools
anthropic           -> subscriptions / dev-tools
jetbrains           -> subscriptions / dev-tools
1password           -> subscriptions / saas
notion              -> subscriptions / saas
linear              -> subscriptions / saas
slack               -> subscriptions / saas

# --- Shopping --------------------------------------------------------------
amazon              -> shopping / electronics
ebay                -> shopping
ikea                -> shopping / furniture
zara                -> shopping / clothing
h&m                 -> shopping / clothing
hm.com              -> shopping / clothing
mediamarkt          -> shopping / electronics
emex                -> shopping / electronics
neptun              -> shopping / electronics
setec               -> shopping / electronics

# --- Health ----------------------------------------------------------------
apoteka             -> health / pharmacy
zegin               -> health / pharmacy
kreston             -> health / pharmacy
sistina             -> health / doctor
acibadem            -> health / doctor
remedika            -> health / doctor

# --- Travel ----------------------------------------------------------------
booking.com         -> travel / hotel
airbnb              -> travel / accommodation
hotels.com          -> travel / hotel
ryanair             -> travel / flight
wizzair             -> travel / flight
turkish airlines    -> travel / flight
lufthansa           -> travel / flight

# --- Insurance ------------------------------------------------------------
triglav             -> insurance
sava osiguruvanje   -> insurance
croatia osiguruvanje-> insurance
uniqa               -> insurance

# --- Pets -----------------------------------------------------------------
zoolife             -> pets / supplies
pet shop            -> pets / supplies
veterinar           -> pets / vet
```

## Vendor aliases

Some payment processors / bank statements show a different merchant string than the receipt does. List `<alias> = <canonical>` so the rule list above stays clean. If you don't recognize a string, leave it for the human review queue rather than guessing.

```
# Examples — extend as you encounter them.
# anton d.o.o.        = lidl       # some Lidl stores are billed under their corporate entity
# tipo komerc         = tinex
# pp paypal           = paypal     # generic PayPal forwarder; downstream rule must categorize the underlying merchant
# google *youtube     = youtube premium
# apple.com/bill mac  = apple.com/bill
```

## Defaults

- If no rule matches, the agent uses `category = "uncategorized"`. The user re-categorizes from the dashboard, and a future rule should be added here.
- Receipts without a clear amount, date, or currency are skipped, never categorized.
- Currency is read from the receipt; if absent, the agent reports the receipt as a skip.
- Subcategory is optional; only emit one when the rule supplies it.
