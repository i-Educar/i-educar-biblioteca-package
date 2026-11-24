# ROADMAP — Biblioteca Escolar

> Pacote: `buriti/i-educar-biblioteca-package` · TR **5.5** · Prioridade **P1** · Nota **C**  
> Alinhado a `tmp/DOC-EXECUTIVO-PE-77-2026-LAGUNA-IEDUCAR.md`

## Visão

Modernizar acervo multiunidade, empréstimo, leitor/webcam e consulta online ao nível do TR.

## Fases

### F0 · Auditoria legado

O que já atende 5.5.x.

### F1 · UX moderna

Telas Laravel/package sobre dados existentes ou migração.

### F2 · Multiunidade + leitor

Fluxos do TR.

### F3 · Consulta online

Portal de busca.

### F4 · PoC

Demo ≥ 85%.

## Cobertura 100% dos requisitos (TR)

Fonte: [`REQUISITOS-TR.md`](./REQUISITOS-TR.md) — **27 itens**.

Cada lote deve sair da PoC como **atende** ou entrar no plano de 30 dias.

### Lote R1 — itens 5.5.1 … 5.5.16 (15)

- `5.5.1`, `5.5.2`, `5.5.3`, `5.5.4`, `5.5.5`, `5.5.6`, `5.5.7`, `5.5.8`, `5.5.9`, `5.5.10`, `5.5.12`, `5.5.13`, `5.5.14`, `5.5.15`, `5.5.16`

- [ ] Implementar / validar / evidenciar na matriz PoC

### Lote R2 — itens 5.5.17 … 5.5.28 (12)

- `5.5.17`, `5.5.18`, `5.5.19`, `5.5.20`, `5.5.21`, `5.5.22`, `5.5.23`, `5.5.24`, `5.5.25`, `5.5.26`, `5.5.27`, `5.5.28`

- [ ] Implementar / validar / evidenciar na matriz PoC

### Gate 100%

- [ ] Todos os lotes R* com status atende ou gap≤30dias
- [ ] Soma atende ≥ 85% na demonstração

## Conformidade Laravel / pacote i-Educar 2.12

Antes da fase de código:

1. Criar `composer.json` com `php >=8.3`, `illuminate/support`, provider em `extra.laravel`, `compatible: ["2.12"]`.
2. Scaffold: `src/Providers`, `Http/Controllers`, `Services`, `Support`, `database/migrations`, `routes/web.php`, views Blade.
3. Schema `preferir modernizar/envolver módulo legado de biblioteca do 2.12; schema novo só se migração for inevitável` (ou estratégia documentada); FKs lógicas para núcleo (`ref_cod_aluno`, `ref_cod_escola`, etc.).
4. Menu/processos em migration própria; respeitar multi-tenant.
5. Não acoplar UI mobile; dados para lojas só via ``buriti/i-educar-apps-api-package``.
6. Seguir `packages/buriti/PADRAO-PACOTE-2.12.md`.

## Dependências

- Cadastro mestre de aluno/escola/turma no i-Educar (SGE).
- Matriz item a item OCR do Anexo I antes de cravar %.
- Alinhamento com `i-educar-apps-api-package` (APIs 5.13/5.14; UI/lojas em equipe externa) e exports (4.x) quando o módulo expõe dados ao cidadão/SME.

## Riscos

- Valor global do edital incompatível com greenfield total — reuso e MVP demonstrável são obrigatórios.
- PDF escaneado: numerações OCR podem exigir reconciliação manual.
- PoC só conta o que estiver operacional no dia.

## Definição de pronto da fase PoC

Checklist do `TODO.md` marcado para a fatia MVP + roteiro de demonstração ensaiado.
