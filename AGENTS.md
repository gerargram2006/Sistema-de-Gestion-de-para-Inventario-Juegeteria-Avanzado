# AGENTS.md

## Qué es este repositorio

Tiene dos entregables de primer nivel, ambos importantes:

- **Aplicación Laravel 13** de inventario y punto de venta para la juguetería "Mundo del Juguete", en la raíz (`app/`, `routes/`, `resources/views/`, `database/`). Nombres de dominio, tablas, mensajes y UI en español.
- **Informe académico LaTeX** en `docs/` (APA 7, español). `docs/` es fuente, no salida generada: no borres diagramas, tablas ni texto para "ahorrar" páginas.

`src/` no existe. Varios archivos de reglas antiguos dicen que Laravel es trabajo futuro; es falso.

## Puesta en marcha

- Requisitos: PHP >= 8.3, Composer 2 y Node/npm. Verificado en este entorno: PHP 8.5, Laravel 13.35, Node 22.
- `composer setup`: instala dependencias PHP, crea `.env`, genera la clave, migra, instala npm y compila assets.
- `php artisan migrate --seed`: crea el esquema y carga usuarios demo y catálogo.
- El `.env` local usa **MariaDB**; `.env.example` y PHPUnit usan SQLite. Las pruebas no necesitan MariaDB.
- Usuarios demo de `database/seeders/UserSeeder.php`: `admin1` / `Admin123*`, `vendedor1` / `Venta123*`, `cajero1` / `Caja123*`, `almacen1` / `Almacen123*`.

## Comandos

- `composer dev` ejecuta `php artisan dev` (runner de desarrollo de Laravel). `npm run dev` solo levanta Vite.
- `composer test` limpia configuración y ejecuta `php artisan test`. Úsalo tras cambiar `.env`.
- `php artisan test`: 36 pruebas, ~90 s, SQLite en memoria.
- Una sola prueba: `php artisan test --filter=test_cajero_puede_cobrar_ticket_y_se_descuenta_el_stock_fisico` o `php artisan test tests/Feature/VentaPosTest.php`.
- Formato PHP: `vendor/bin/pint` (sin `pint.json`, preset por defecto de Laravel).
- Build frontend: `npm run build`. Si falla con `Cannot find native binding` o `@rolldown/binding-*`, el `node_modules` se instaló en Windows; ejecuta `npm install` dentro de WSL o compila en Windows.

## Mapa de Laravel

- Rutas web: `routes/web.php` (no hay API). Autenticación Breeze: `routes/auth.php`.
- Alias de middleware `role` registrado en `bootstrap/app.php`; implementación en `app/Http/Middleware/CheckRole.php`.
- Roles: `administrador`, `vendedor`, `cajero`, `almacenero` (`User::ROL_*`).
- Accesos:
  - `dashboard`: cualquier usuario autenticado.
  - `inventario`: lectura para autenticados; crear/editar solo `administrador,almacenero`.
  - `ventas` y `ventas/pos`: `vendedor,administrador`.
  - `caja`: `cajero,administrador`.
  - `abastecimiento`: `almacenero,administrador`.
- Vistas: `resources/views/{ventas,caja,abastecimiento,inventario}`; layout compartido `layouts/app.blade.php`; el menú se filtra por rol en `layouts/navigation.blade.php`.
- Modelos, tablas y claves primarias no estándar (siempre pasa las FKs explícitamente):
  - `Producto` -> `productos` / `id_producto`
  - `Categoria` -> `categorias` / `id_categoria`
  - `Cliente` -> `clientes` / `id_cliente`
  - `Venta` -> `ventas` / `id_venta`
  - `DetalleVenta` -> `detalle_ventas`
  - `Compra` -> `compras` / `id_compra`
  - `DetalleCompra` -> `detalle_compras`
  - `User` -> `users` / `id` (el rol vive en `rol`)
- `User` usa atributos de Laravel 13 `#[Fillable]` y `#[Hidden]`, no `$fillable`.

## Reglas de negocio que no debes romper

- El POS (`VentaController@store`) crea la venta en estado `pendiente` y valida stock, pero **no descuenta existencias**.
- Solo `CajaController@cobrar` descuenta stock, dentro de una transacción con `lockForUpdate`, marca la venta como `pagada` y genera comprobante `B001-` o `F001-`.
- `AbastecimientoController@recibirOrden` incrementa stock y marca la compra como `recibida`.
- `Producto::actualizarEstadoStock()` define `disponible` / `stock bajo` / `agotado`. `InventarioController@store` duplica esos umbrales; si cambias uno, cambia el otro.
- Los precios incluyen IGV; los accessors de `Venta` calculan `subtotal` e `igv` sobre `total / 1.18`.
- Solo existe `UserFactory`. Las pruebas de dominio crean `Categoria`, `Producto` y `Cliente` con `Model::create([...])`.
- Los textos de interfaz, validaciones y mensajes van en español.

## Informe académico (`docs/`)

- Archivo principal: `docs/Proyecto.tex`. Incluye 17 tablas de `docs/tablas/` y 23 diagramas TikZ de `docs/diagramas/`; las imágenes viven en `docs/media/`. Edita el archivo modular, no copies contenido inline (no hay `tikzpicture` inline).
- Compilar desde `docs/`:
  ```sh
  "/mnt/c/Program Files/MiKTeX/miktex/bin/x64/latexmk.exe" -pdf -interaction=nonstopmode -halt-on-error -outdir=scratch/build Proyecto.tex
  ```
  En WSL no hay `latexmk` ni `pdflatex` en el PATH; la ruta de MiKTeX indicada es la que funciona. En Windows sirve `latexmk -pdf Proyecto.tex` (o `pdflatex` dos veces).
- Línea base verificada: 98 páginas, 44 figuras, 36 tablas, 0 `Overfull \hbox`. Tras cambios de maquetación comprueba:
  `grep -c "contentsline {figure}" Proyecto.lof` = 44 y `grep -c "contentsline {table}" Proyecto.lot` = 36.
- **No elimines diagramas, tablas ni contenido** al reorganizar. El usuario exige conservar todo lo avanzado.
- Formato fijo: A4, márgenes de 2.5 cm, 12 pt, doble espacio, sans-serif Helvetica/Arial (`helvet`), APA 7 (títulos de tabla arriba, de figura abajo), número de página abajo al centro. No cambies a Times ni alteres estos parámetros sin pedirlo.
- Tablas: marco completo con `\hline`, `tabularx`/`longtable`; nunca `\rowcolor` ni `\columncolor` (conflictos con `colortbl`). Las fichas CUN son `tabularx` a ancho completo con `p{3.2cm}` + `X`; la regla antigua de 4 cm/11.5 cm ya no aplica.
- `docs/*.pdf`, `.aux`, `.toc`, `.lot`, `.lof`, `.log`, `.fls`, `.fdb_latexmk` y `.synctex.gz` están ignorados por Git. Compila en `docs/scratch/build/` y copia el PDF solo para revisión local; no hagas commit de artefactos.
- Para aprovechar espacio, prefiere quitar `\clearpage` innecesarios y usar `\needspace{...}` antes de títulos cortos; no reduzcas contenido.

## Convenciones y archivos obsoletos

- Commits: Conventional Commits en español, atómicos, por ejemplo `feat(caja): ...`, `fix(tablas): ...`.
- README: tono profesional y académico, sin emojis y sin enlaces directos a `Proyecto.pdf`; mantenlo sincronizado si cambia la estructura.
- `.cursorrules`, `.agents/rules/AGENTS.md` y `.github/copilot-instructions.md` están desactualizados: afirman que Laravel es futuro, que no hay LaTeX, que se usa Times y que el número de página va arriba a la derecha. Confía en este archivo y en el código.
- `CLAUDE.md` es una copia del antiguo bootstrap de Laravel Boost. `laravel/boost` **no** está instalado; no ejecutes `composer require laravel/boost` salvo que el usuario lo pida.
- Ignora las referencias a `src/`: ese directorio no existe.
