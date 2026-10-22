<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Sistema de Gestión de Inventario para Juguetería

Proyecto de **Ingeniería de Requerimientos y Diseño de Software** — Entregable:
**Modelo de negocio (Parte 3)**. Aplica el modelado de negocio y los principios
de la **Programación Orientada a Objetos (POO)** para el diseño de una
plataforma de inventario de una juguetería minorista, empleando PHP y Laravel como
stack de implementación bajo la arquitectura **Modelo-Vista-Controlador (MVC)**.

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
| Lenguaje de servidor | PHP 8.2+ |
| Framework web | Laravel (MVC) |
| Persistencia de datos | Eloquent ORM + MySQL / MariaDB |
| Motor de plantillas | Blade (HTML5, CSS3, JavaScript) |
| Entorno de desarrollo | Composer, Vite, PHP Artisan |

## Estructura del Proyecto

El repositorio integra tanto la base de la aplicación Laravel como la documentación formal en LaTeX:

```
├── app/               Modelos Eloquent, controladores MVC y lógica de negocio
├── bootstrap/         Archivos de inicio y arranque del framework
├── config/            Configuración modular de la aplicación
├── database/          Migraciones, seeders y fábricas de base de datos
├── docs/              Documento académico de investigación en formato APA 7.ª edición
│   ├── media/         Diagramas UML, prototipos UI/UX y evidencias gráficas
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
- Diagrama de clases del dominio y modelo relacional (10 clases, tipado estricto, multiplicidades y composiciones).
- Diagrama de integración y modelo de dominio conceptual.
- Diagrama de Gantt del plan de ingeniería de requerimientos.
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
   - 3.5 Quinto grupo de entregables (Semana 13): Diagramas de diseño (paquetes en 3 capas, despliegue, componentes, secuencia BCE en orientación horizontal, diagramas de estados, modelo de dominio relacional y ERD conceptual) y cronograma del proyecto (diagrama de Gantt).
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
- PHP 8.2 o superior instalado.
- Composer 2.x instalado.
- Servidor de base de datos MySQL o MariaDB.

### Pasos de Configuración

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/gerargram2006/Sistema-de-Gestion-de-para-Inventario-Juegeteria-Avanzado.git
   cd Sistema-de-Gestion-de-para-Inventario-Juegeteria-Avanzado
   ```

2. **Instalar dependencias de PHP:**
   ```bash
   composer install
   ```

3. **Configurar el archivo de entorno:**
   ```bash
   cp .env.example .env
   ```

4. **Generar la clave de seguridad de la aplicación:**
   ```bash
   php artisan key:generate
   ```

5. **Configurar la base de datos:**
   Editar el archivo `.env` con las credenciales de conexión locales (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

6. **Ejecutar migraciones (cuando aplique):**
   ```bash
   php artisan migrate
   ```

7. **Iniciar el servidor local de desarrollo:**
   ```bash
   php artisan serve
   ```
   La aplicación quedará accesible en `http://localhost:8000`.

## Conclusiones y Recomendaciones

El documento expone conclusiones fundamentales sobre la utilidad del modelado del
negocio para comprender los flujos actuales de la tienda y definir los
requerimientos del sistema. Asimismo, se establecen recomendaciones orientadas a 
las validaciones estrictas de stock, auditorías, automatización de reportes y 
la inducción operativa del personal.

## Estrategia de Control de Versiones (GIT)

El proyecto se gestiona de forma colaborativa a través de un repositorio en GitHub, 
con un historial superior a **70 commits**. Se utiliza un archivo `.gitignore` para mantener 
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

**Curso:** Ingeniería de Requerimientos y Diseño de Software — Docente: Olanda Saavedra, Claudio

## Enlaces

- **Repositorio GitHub:** <https://github.com/gerargram2006/Sistema-de-Gestion-de-para-Inventario-Juegeteria-Avanzado.git>

## Referencias

- Banker, K., Bakkum, P., Verch, S., Garrett, D., y Hawkins, T. (2016). *MongoDB in action* (2.ª ed.). Manning Publications.
- Bradshaw, S., Brazil, E., y Chodorow, C. (2019). *MongoDB: The definitive guide* (3.ª ed.). O'Reilly Media.
- Pressman, R. S., y Maxim, B. R. (2021). *Ingeniería del software: Un enfoque práctico* (9.ª ed.). McGraw-Hill Education.
- Wiegers, K., y Beatty, J. (2013). *Software requirements* (3.ª ed.). Microsoft Press.

## Créditos

Material audiovisual de apoyo para la configuración del entorno LaTeX en Visual Studio Code:

- Ayudantías. (s. f.). *Cómo instalar LaTeX en VSCode* [Video]. YouTube. [https://www.youtube.com/watch?v=9w7eb56bF7Y](https://www.youtube.com/watch?v=9w7eb56bF7Y)
