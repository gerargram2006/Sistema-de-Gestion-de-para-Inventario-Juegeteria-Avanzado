<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Sistema de Gestión de Inventario para Juguetería

[![Pruebas](https://github.com/gerargram2006/Sistema-de-Gestion-de-para-Inventario-Juegeteria-Avanzado/actions/workflows/tests.yml/badge.svg)](https://github.com/gerargram2006/Sistema-de-Gestion-de-para-Inventario-Juegeteria-Avanzado/actions/workflows/tests.yml)

Sistema web de inventario, ventas, caja, abastecimiento y devoluciones para la
juguetería **"Mundo del Juguete"**. Es un proyecto integrador de tres cursos:

| Curso | Aporte al proyecto |
|-------|--------------------|
| **Ingeniería de Requerimientos y Diseño de Software** | Modelo de negocio (CUN), requerimientos RF/RNF, casos de uso del sistema (CUS), diagramas UML y plan de despliegue (`docs/Proyecto.tex`). |
| **Base de Datos Avanzado** | Modelo relacional normalizado en MySQL 8.0: llaves foráneas con `RESTRICT`, restricciones `CHECK`, índices compuestos y transacciones con bloqueo de filas. |
| **Desarrollo de Aplicaciones en Internet** | Aplicación web en Laravel 13 (MVC) con autenticación, control de acceso por roles y pruebas automatizadas. |

## Problemática

El control manual de existencias y el uso de hojas de cálculo aisladas generan los siguientes desafíos:

- Deficiencias en el **control de stock por categorías y marcas**.
- **Invisibilidad de existencias en tiempo real** (falta de sincronización entre ventas y almacén).
- **Ineficiencia** operativa en los procesos manuales y auditorías físicas.

## Solución Propuesta

Desarrollo de una aplicación web modular, escalable y portable que centraliza las operaciones
en un solo sistema, garantizando agilidad, un control preciso de existencias (stock
y precios), auditorías confiables y generación de reportes para la toma de decisiones.

## Arquitectura del Sistema

| Capa | Tecnología |
|------|------------|
| Lenguaje de servidor | PHP 8.4 |
| Framework web | Laravel 13 (MVC) + Laravel Breeze (autenticación) |
| Persistencia de datos | Eloquent ORM + MySQL 8.0 (InnoDB) |
| Interfaz | Blade, Tailwind CSS 3 y Alpine.js |
| Herramientas | Composer, npm + Vite, PHP Artisan |
| Pruebas | PHPUnit 12 + GitHub Actions (SQLite y MySQL 8.0) |
| Servidor de producción | Windows 11 Pro + Apache 2.4 |

## Módulos de la Aplicación

| Módulo | Ruta | Roles | Descripción |
|--------|------|-------|-------------|
| Panel principal | `/dashboard` | Todos | KPIs del día, ventas recientes y alertas de stock crítico. |
| Catálogo e inventario | `/inventario` | Todos (edición: administrador y almacenero) | Registro y edición de juguetes, filtro por categoría y stock crítico, baja y reactivación de productos. |
| Punto de venta (CUS-01) | `/ventas/pos` | Vendedor, administrador | Registro de la venta y generación del ticket `TK-AAAAMMDD-NNNNNN`. |
| Caja y cobros (CUS-02) | `/caja` | Cajero, administrador | Cobro del ticket, descuento de stock y emisión de boleta (`B001`) o factura (`F001`) con correlativo propio por serie. |
| Abastecimiento (CUS-03/04) | `/abastecimiento` | Almacenero, administrador | Órdenes de compra `OC-AAAAMMDD-NNNNNN` sugeridas por stock crítico y recepción de mercadería. |
| Devoluciones y cambios (CUS-06) | `/devoluciones` | Vendedor, cajero, administrador | Reembolso o cambio dentro del plazo de 7 días (RF-19), o rechazo con motivo; ajusta el inventario. |
| Usuarios y roles | `/usuarios` | Administrador | Alta de trabajadores, asignación de rol, baja/reactivación de cuentas y generación de códigos de restablecimiento (no hay registro público). |
| Restablecer contraseña | `/restablecer` | Público | El trabajador define una nueva contraseña con un código de un solo uso que le entrega el administrador. |

## Base de Datos (MySQL 8.0)

- **10 tablas de negocio:** `users`, `clientes`, `categorias`, `productos`, `ventas`, `detalle_ventas`, `compras`, `detalle_compras`, `devoluciones` y `tokens_restablecimiento`, además de las tablas de soporte de Laravel (sesiones, caché y colas).
- **Integridad referencial:** las llaves foráneas usan `ON DELETE RESTRICT`, por lo que no se puede borrar una categoría con juguetes ni un juguete con ventas (se da de baja con el estado `inactivo`).
- **Restricciones `CHECK`:** 11 reglas de dominio a nivel de motor: precio de venta mayor que cero, stock no negativo, cantidades mayores que cero y totales, costos y subtotales no negativos (migración `agregar_restricciones_check`).
- **Índices compuestos:** sobre `ventas(estado, fecha)`, `compras(estado, fecha)`, `productos(estado, nombre)` y otros, verificados con `EXPLAIN` (de recorrido completo `ALL` a acceso por índice `ref`).
- **Concurrencia:** el cobro, la recepción de compras y las devoluciones se ejecutan en transacciones con `SELECT ... FOR UPDATE`, lo que evita el doble cobro y el stock negativo.

## Seguridad (RNF-03)

- **Sin registro público:** las cuentas las crea el administrador y una cuenta dada de baja no puede iniciar sesión ni continuar una sesión abierta.
- **Contraseñas:** mínimo 8 caracteres con mayúsculas, minúsculas y números; se guardan con *hash* bcrypt.
- **Restablecimiento con token de un solo uso:** el administrador genera un código (`ABCD-EFGH-JKMN`) y lo entrega en persona. De ese código solo se guarda su *hash*; vence a los 30 minutos, sirve una sola vez y al usarlo se cierran las sesiones abiertas de esa cuenta en otros equipos. La tienda no tiene servidor de correo, por lo que el envío de enlaces de recuperación está deshabilitado.
- **Límite de intentos:** 5 intentos por minuto tanto en el inicio de sesión como en el restablecimiento, con mensajes que no revelan si la cuenta existe.
- **Cabeceras HTTP:** `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy` y `Cache-Control: no-store` en las pantallas con sesión iniciada.
- **Control de acceso por rol:** cada módulo está protegido con el *middleware* `role:` y las consultas usan Eloquent (sin SQL concatenado).

## Estructura del Proyecto

El repositorio integra tanto la base de la aplicación Laravel como la documentación formal en LaTeX:

```
├── app/               Modelos Eloquent, controladores MVC y lógica de negocio
├── bootstrap/         Archivos de inicio y arranque del framework
├── config/            Configuración modular de la aplicación
├── database/          Migraciones, seeders y fábricas de base de datos
├── docs/              Documento académico de investigación en formato APA 7.ª edición
│   ├── diagramas/     Fuentes modulares TikZ (secuencia, estados, paquetes, despliegue, ER)
│   ├── media/         Diagramas UML, prototipos UI/UX y evidencias gráficas
│   ├── tablas/        Tablas modulares (RF/RNF, CUN, CUS, matrices y diccionario de datos)
│   └── Proyecto.tex   Código fuente LaTeX del informe completo
├── public/            Punto de entrada web (index.php) y recursos públicos
├── resources/         Vistas Blade, hojas de estilo CSS y scripts JS
├── routes/            Definición de rutas web y endpoints de consola
├── storage/           Almacenamiento de archivos temporales, logs y sesiones
├── tests/             Pruebas automatizadas unitarias y de integración (PHPUnit)
└── .gitignore         Filtros de control de versiones para Laravel y LaTeX
```

## Modelado de Negocio

### Casos de Uso del Negocio (CUN)
Especificaciones completas con precondiciones, secuencias, flujos alternativos, postcondiciones y objetivos:

1. **CUN-01:** Gestionar ventas (registrar venta).
2. **CUN-02:** Gestionar abastecimiento.
3. **CUN-03:** Controlar inventario.
4. **CUN-04:** Gestionar devoluciones y cambios.
5. **CUN-05:** Flujo de venta y reposición (trabajadores del negocio).
6. **CUN-06:** Flujo de venta y reposición (actores externos).

### Actores del Sistema
- **Vendedor:** Registro y atención directa de transacciones en tienda.
- **Administrador:** Gestión de compras, auditoría de stock y configuración.
- **Cajero:** Cobro de compras, validación de métodos de pago y facturación legal.
- **Encargado de almacén:** Recepción de mercancía física y control de existencias en bodega.

### Entidades del Negocio
Usuarios, clientes, categorías, inventario de productos, ventas, detalle\_ventas, devoluciones, compras y detalle\_compras.

### Requerimientos y Trazabilidad
- **Requerimientos Funcionales (RF):** 31 requerimientos distribuidos en los casos de uso.
- **Requerimientos No Funcionales (RNF):** 6 requerimientos clave (rendimiento, disponibilidad, seguridad, concurrencia, usabilidad y respaldo).
- **Matriz de Trazabilidad:** Relación cruzada entre requisitos (RF/RNF), Casos de Uso del Sistema (CUS), entidades del modelo de dominio y prototipos de interfaz.

### Diagramas Desarrollados
- Diagramas de clases por entidad conceptual.
- Diagrama de clases de negocio y paquetes arquitectónicos.
- Diagramas de actividades por cada caso de uso del negocio (CUN-01 al CUN-06).
- Diagramas de casos de uso del sistema (CUS) clasificados por actor operativo.
- Diagramas de secuencia del sistema (CUS-01 al CUS-04) bajo el patrón Boundary-Control-Entity (BCE) generados nativamente en TikZ en orientación apaisada/horizontal.
- Diagramas de estados de entidades y procesos clave (Ventas, Compras, Devoluciones, Inventario, Recepción) renderizados en TikZ.
- Diagrama de paquetes de software en arquitectura de 3 capas (Presentación, Lógica de Negocio y Dominio) con dependencias dirigidas.
- Diagrama de despliegue UML (nodos cliente POS/PC, router/firewall, servidor de aplicación Laravel/Apache, servicios externos, servidor MySQL y respaldo en la nube) renderizado nativamente en TikZ.
- Diagrama de clases del dominio y modelo relacional (10 clases, tipado estricto, multiplicidades y composiciones).
- Modelo de datos relacional normalizado en TikZ (9 tablas con cardinalidades 1 a N) y diccionario de datos completo.
- Diagrama de integración y modelo de dominio conceptual.
- Diagrama de Gantt para la planificación del análisis de requerimientos (Semana 7).
- Diagrama de Gantt: Cronograma integral de desarrollo del proyecto en Cascada (Semana 13).
- Diagrama de Gantt del plan de despliegue y pase a producción (Semana 13).
- Diagrama de despliegue en producción con arquitectura física de tres nodos (Terminales POS/PC, Servidor de aplicaciones Apache/Laravel, Servidor MySQL y respaldo en la nube).
- Prototipos de pantallas UI/UX (Login, Ventas, Pago, Abastecimiento y Almacén).
- Diagrama entidad-relación (ERD conceptual).

### Estructura Capitular del Documento Académico (`docs/Proyecto.tex`)

El documento académico sigue estrictamente la guía-plantilla oficial de TECSUP, estructurada en 7 capítulos:

1. **Capítulo 1: Justificación del Proyecto**
   - 1.1 Planteamiento del problema general y justificación del proyecto.
   - 1.2 Objetivo general.
   - 1.3 Objetivos específicos (OE1 al OE4).
2. **Capítulo 2: Marco Teórico Referencial o Diseño Metodológico**
   - 2.1 Marco teórico y tecnológico (POO, Arquitectura MVC, Laravel, Eloquent ORM).
   - 2.2 Explicación metodológica del modelo en cascada (Waterfall).
3. **Capítulo 3: Desarrollo de la Solución**
   - 3.1 Primer grupo de entregables (Semana 5): Organización objetivo, actores, trabajadores, CUN y entidades del negocio.
   - 3.2 Segundo grupo de entregables (Semana 7): Modelo del negocio (Diagramas CUN-01 a CUN-06), Requerimientos Funcionales y No Funcionales, matrices de trazabilidad y especificación de alto y bajo nivel.
   - 3.3 Tercer grupo de entregables (Semana 09): Prototipos de pantallas (UI/UX) y enlaces a prototipos interactivos.
   - 3.4 Cuarto grupo de entregables (Semana 10): Casos de uso del sistema (CUS-01 a CUS-04), actores del sistema y demostración del avance de interfaces.
   - 3.5 Quinto grupo de entregables (Semana 13): Diagramas de diseño (paquetes en 3 capas, despliegue UML, componentes, secuencia BCE en orientación horizontal, diagramas de estados, modelo de dominio relacional, ERD conceptual, modelo de datos normalizado de 9 tablas y diccionario de datos), cronograma del proyecto (diagramas de Gantt) y plan integral de despliegue a producción (arquitectura física en 3 nodos, estrategia piloto, matriz de responsables, pruebas en entorno final, release notes v1.0, capacitación, migración/rollback y análisis de riesgos).
   - 3.6 Sexto grupo de entregables (Semana 15): Matriz formal de casos de prueba del proyecto (CP-01 a CP-06 con sustento y evidencias).
4. **Capítulo 4: Conclusiones**
   - Conclusiones evaluadas en correspondencia directa con los objetivos específicos y la metodología aplicada.
5. **Capítulo 5: Recomendaciones**
   - Recomendaciones técnicas y de gestión formuladas en correspondencia biunívoca con las conclusiones.
6. **Capítulo 6: Referencias Bibliográficas**
   - Fuentes académicas en estricto cumplimiento del formato APA 7.ª edición.
7. **Capítulo 7: Anexos**
   - Anexo 1: Repositorio en GitHub y estrategia de control de versiones.
   - Anexo 2: Estructura del proyecto Laravel y entorno de ejecución.

## Instalación y Ejecución de la Aplicación

Para desplegar y ejecutar localmente la aplicación Laravel:

### Requisitos Previos
- **PHP 8.4** o superior, con las extensiones `pdo_mysql`, `mbstring` y `bcmath`.
- **Composer 2.x**.
- **Node.js 20+ y npm** (para compilar los estilos y scripts con Vite).
- **MySQL 8.0** (o MariaDB 10.2+).

### Pasos de Configuración

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/gerargram2006/Sistema-de-Gestion-de-para-Inventario-Juegeteria-Avanzado.git
   cd Sistema-de-Gestion-de-para-Inventario-Juegeteria-Avanzado
   ```

2. **Instalar dependencias y compilar los assets:**
   ```bash
   composer install
   npm install
   npm run build
   ```

3. **Configurar el entorno y generar la clave de la aplicación:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Crear la base de datos** `bd_mundojuguete` en MySQL y, si es necesario, ajustar
   `DB_USERNAME` y `DB_PASSWORD` en el archivo `.env`:
   ```sql
   CREATE DATABASE bd_mundojuguete CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

5. **Crear las tablas y cargar los datos de demostración:**
   ```bash
   php artisan migrate --seed
   ```

6. **Iniciar el servidor local de desarrollo:**
   ```bash
   php artisan serve
   ```
   La aplicación quedará accesible en `http://localhost:8000`.

### Usuarios de Demostración

El seeder crea un usuario por cada rol (solo para entornos de desarrollo):

| Rol | Correo | Contraseña |
|-----|--------|------------|
| Administrador | `admin@mundojuguete.pe` | `Admin123*` |
| Vendedor | `vendedor@mundojuguete.pe` | `Venta123*` |
| Cajero | `cajero@mundojuguete.pe` | `Caja123*` |
| Encargado de almacén | `almacen@mundojuguete.pe` | `Almacen123*` |

> No existe registro público: las cuentas nuevas las crea el administrador desde el módulo **Usuarios**.

### Pruebas Automatizadas

```bash
php artisan test
```

Por defecto las pruebas usan SQLite en memoria. Para ejecutarlas contra MySQL (donde
además se validan las restricciones `CHECK`), cree una base de datos de prueba y exporte
las variables antes de correrlas:

```bash
DB_CONNECTION=mysql DB_DATABASE=bd_mundojuguete_test php artisan test
```

GitHub Actions ejecuta ambas variantes en cada *push* a `main` y en cada *pull request*
(`.github/workflows/tests.yml`).

## Conclusiones y Recomendaciones

El documento expone conclusiones fundamentales sobre la utilidad del modelado del
negocio para comprender los flujos actuales de la tienda y definir los
requerimientos del sistema. Asimismo, se establecen recomendaciones orientadas a 
las validaciones estrictas de stock, auditorías, automatización de reportes y 
la inducción operativa del personal.

## Estrategia de Control de Versiones (GIT)

El proyecto se gestiona de forma colaborativa a través de un repositorio en GitHub, 
con un historial superior a **150 commits**. Los mensajes siguen el formato
`tipo(alcance): descripción` (`feat`, `fix`, `docs`, `test`, `chore`, `ci`...), con un commit por cada cambio relevante. Se utiliza un archivo `.gitignore` para mantener 
el repositorio limpio de metadatos locales y la presente documentación como base del desarrollo del sistema.

> **Nota para colaboradores:** Antes de integrar código o realizar modificaciones, **ejecute siempre `git pull`** 
> para sincronizar los cambios más recientes. Esta práctica previene conflictos de integración y sobreescritura accidental.

## Instrucciones para Asistentes de IA

Este repositorio cuenta con reglas estrictas de formato y comportamiento predefinidas para los agentes de Inteligencia Artificial (ej. Antigravity, GitHub Copilot, Cursor, Windsurf). Estas instrucciones garantizan que cualquier código o documentación generada respete la estructura del proyecto, el formato APA 7.ª edición, la limpieza del código y nuestro flujo de control de versiones. 
Las IAs leen automáticamente estas directrices desde los archivos de configuración nativos ocultos (`.agents/rules/AGENTS.md`, `.github/copilot-instructions.md` y `.cursorrules`). No es necesario repetirles las reglas de diseño o de GIT en cada prompt; los asistentes ya están totalmente contextualizados para trabajar bajo los estándares de este equipo.

## Equipo de Desarrollo

- Gerar Rodrigo Camma Baldeon
- Hugo Ernesto Florez Merma
- Barry Valdivia Lovon
- Samuel A. Llallacachi Salhua
- Simon Rodriguez Anculle

**Institución:** TECSUP · Arequipa, Perú — 2026 · Ciclo III

**Cursos:** Ingeniería de Requerimientos y Diseño de Software (Docente: Olanda Saavedra, Claudio) · Base de Datos Avanzado · Desarrollo de Aplicaciones en Internet

## Enlaces

- **Repositorio GitHub:** <https://github.com/gerargram2006/Sistema-de-Gestion-de-para-Inventario-Juegeteria-Avanzado.git>

## Referencias

- Laravel. (2026). *Laravel 13.x documentation*. <https://laravel.com/docs/13.x>
- Oracle Corporation. (2026). *MySQL 8.0 reference manual*. <https://dev.mysql.com/doc/refman/8.0/en/>
- Pressman, R. S., y Maxim, B. R. (2021). *Ingeniería del software: Un enfoque práctico* (9.ª ed.). McGraw-Hill Education.
- Wiegers, K., y Beatty, J. (2013). *Software requirements* (3.ª ed.). Microsoft Press.

## Créditos

Material audiovisual de apoyo para la configuración del entorno LaTeX en Visual Studio Code:

- Ayudantías. (s. f.). *Cómo instalar LaTeX en VSCode* [Video]. YouTube. [https://www.youtube.com/watch?v=9w7eb56bF7Y](https://www.youtube.com/watch?v=9w7eb56bF7Y)
