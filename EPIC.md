# EPIC — Biblioteca Escolar

| Campo | Valor |
| --- | --- |
| Pacote | `buriti/i-educar-biblioteca-package` |
| TR Laguna | **5.5** |
| Prioridade | **P1** |
| Nota atual (executivo) | **C** |
| Fonte | `tmp/DOC-EXECUTIVO-PE-77-2026-LAGUNA-IEDUCAR.md` |
| Regra de ouro | Pregão Eletrônico 77/2026 · Laguna/SC · PoC ≥ 85% por módulo · gaps ≤ 30 dias |
| Base atual | Módulo legado de biblioteca no i-Educar |

## Objetivo

Modernizar acervo multiunidade, empréstimo, leitor/webcam e consulta online ao nível do TR.

## Padrão técnico (i-Educar 2.12)

| Item | Valor |
| --- | --- |
| Alvo | i-Educar **2.12** · PHP **≥ 8.3** · Illuminate **^11|^12|^13** (Laravel 13 no app) |
| Composer | `buriti/i-educar-biblioteca-package` |
| Namespace | `iEducar\Packages\BuritiBiblioteca\` → `src/` |
| Compat | `extra.ieducar.compatible: ["2.12"]` · GPL-2.0-or-later · `type: library` |
| Schema / dados | preferir modernizar/envolver módulo legado de biblioteca do 2.12; schema novo só se migração for inevitável |
| Rotas | prefixo `biblioteca`; SME com middleware `ieducar.*` + `auth`; API apps via Sanctum quando exposto |
| Tenancy | `PackageTenant::allows('buriti/i-educar-biblioteca-package')` antes de migrations/menu |
| Plug-and-play | path em `packages/buriti/…`; manter em `ignore` até instalável |
| Referência | `packages/buriti/PADRAO-PACOTE-2.12.md` |

**Nota de arquitetura:** Auditar legado antes de reescrever; faixas de menu fora de conflitos Portábilis.

## Cobertura 100% do TR

Este EPIC cobre **todos** os requisitos do módulo(s) 5.5 listados em [`REQUISITOS-TR.md`](./REQUISITOS-TR.md) (27 itens OCR).

- PoC: ≥ **85%** demonstráveis e operacionais.
- Até **15%** restante: produção em ≤ **30 dias**, sem ônus.
- Nenhum item do `REQUISITOS-TR.md` pode ficar fora do `ROADMAP.md` / `TODO.md` (direto ou por grupo rastreável).

## Épicos filhos

1. Acervo multiunidade com exemplares e circulação.
2. Empréstimo/devolução com leitor de código/webcam.
3. Consulta pública/online ao catálogo.
4. Comprovantes e relatórios de circulação.

## Critérios de aceite (PoC)

- [ ] Checklist [`REQUISITOS-TR.md`](./REQUISITOS-TR.md) com **100%** dos itens classificados (atende / gap / N/A)
- [ ] Itens **atende** demonstráveis na PoC (≥ 85% do módulo 5.5)
- [ ] Itens **gap** ≤ 15% com plano ≤ 30 dias em produção, sem ônus
- [ ] Nenhum requisito do TR omitido da matriz


## Fora de escopo deste EPIC

- Orçamento comercial da proposta Laguna.
- Substituição do core i-Educar fora do domínio do módulo.
