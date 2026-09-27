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

## Arquitectura

| Capa | Tecnología |
|------|------------|
| Lenguaje de servidor | PHP |
| Framework | Laravel (MVC) |
| Persistencia | Eloquent ORM + MySQL |
| Vistas | Blade + Bootstrap (HTML5, CSS, JavaScript) |

## Estructura del Proyecto

```
docs/   Documento de investigación en formato APA 7.ª edición (LaTeX + PDF)
src/    Código fuente de la aplicación (Laravel)
```

## Modelado de Negocio

**Casos de uso del negocio** (con precondiciones, secuencias, flujos alternativos, postcondición y objetivo):

1. CUN-01: Gestionar ventas (registrar venta)
2. CUN-02: Gestionar abastecimiento
3. CUN-03: Controlar inventario
4. CUN-04: Gestionar devoluciones y cambios
5. CUN-05: Flujo de venta y reposición (trabajadores del negocio)
6. CUN-06: Flujo de venta y reposición (actores externos)

**Trabajadores del negocio:** Vendedor, administrador/dueño, cajero y encargado de almacén.

**Entidades del negocio:** Usuarios, clientes, categorías, inventario de productos, ventas, detalle\_ventas, devoluciones, compras y detalle\_compras.

**Reglas de negocio:** Reglas de estímulo y respuesta, de operación, de estructura, de inferencia y de cálculo.

**Requerimientos:** 31 requerimientos funcionales distribuidos en los seis casos de uso y 6 requerimientos no funcionales. La matriz de trazabilidad relaciona cada identificador (RF/RNF) con su respectivo actor, caso de uso o regla de negocio, e incluye la justificación técnica de su necesidad.

**Planificación:** Diagrama de Gantt detallando las actividades de análisis, levantamiento, validación y cierre documental.

**Diagramas Elaborados:**

- Diagramas de clases por entidad (productos, ventas y detalle de ventas, compras y detalle de compras, devoluciones, usuarios y clientes).
- Diagrama de clases de negocio.
- Diagramas de actividades por CUN (ventas, abastecimiento, inventario, devoluciones y cambios, flujo de trabajadores y flujo de actores externos).
- Diagrama de Gantt del análisis de requerimientos.
- Matriz de trazabilidad de requerimientos.
- Diagrama entidad-relación (ERD conceptual).

La representación visual del dominio y las actividades permite analizar la participación 
de cada actor, prever escenarios de error (como roturas de stock o comprobantes no válidos) 
y estructurar formalmente las validaciones del futuro sistema.

## Conclusiones y Recomendaciones

El documento expone conclusiones fundamentales sobre la utilidad del modelado del
negocio para comprender los flujos actuales de la tienda y definir los
requerimientos del sistema. Asimismo, se establecen recomendaciones orientadas a 
las validaciones estrictas de stock, auditorías, automatización de reportes y 
la inducción operativa del personal.

## Estrategia de Control de Versiones (GIT)

El proyecto se gestiona de forma colaborativa a través de un repositorio en GitHub, 
con un historial superior a **30 commits**. Se utiliza un archivo `.gitignore` para mantener 
el repositorio limpio de metadatos locales y la presente documentación como base del desarrollo del sistema.

> **Nota para colaboradores:** Antes de integrar código o realizar modificaciones, **ejecute siempre `git pull`** 
> para sincronizar los cambios más recientes. Esta práctica previene conflictos de integración y sobreescritura accidental.

## Equipo de Desarrollo

- Gerar Rodrigo Camma Baldeon
- Hugo Ernesto Florez Merma
- Barry Valdivia Lovon
- Samuel A. Llallacachi Salhua
- Simon Rodriguez Anculle

**Institución:** TECSUP · Arequipa, Perú — 2026 · Ciclo III

**Curso:** Ingeniería de Requerimientos y Diseño de Software — Docente: Olanda Saavedra, Claudio

## Enlaces

- **Documento de investigación:** [`docs/Proyecto.pdf`](docs/Proyecto.pdf)
- **Repositorio GitHub:** <https://github.com/gerargram2006/Sistema-de-Gestion-de-para-Inventario-Juegeteria-Avanzado.git>

## Referencias

- Banker, K., Bakkum, P., Verch, S., Garrett, D., y Hawkins, T. (2016). *MongoDB in action* (2.ª ed.). Manning Publications.
- Bradshaw, S., Brazil, E., y Chodorow, C. (2019). *MongoDB: The definitive guide* (3.ª ed.). O'Reilly Media.
- Pressman, R. S., y Maxim, B. R. (2021). *Ingeniería del software: Un enfoque práctico* (9.ª ed.). McGraw-Hill Education.
- Wiegers, K., y Beatty, J. (2013). *Software requirements* (3.ª ed.). Microsoft Press.

## Créditos

Material audiovisual de apoyo para la configuración del entorno LaTeX en Visual Studio Code:

- Ayudantías. (s. f.). *Cómo instalar LaTeX en VSCode* [Video]. YouTube. [https://www.youtube.com/watch?v=9w7eb56bF7Y](https://www.youtube.com/watch?v=9w7eb56bF7Y)