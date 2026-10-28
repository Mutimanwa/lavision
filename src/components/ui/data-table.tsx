"use client";

import { useState, type ReactNode } from "react";
import { AlertCircle, Download, RefreshCw, Search, X } from "lucide-react";
import { TablePagination } from "./primitives";

export interface DataTableColumn<T> {
  header: ReactNode;
  key?: keyof T;
  render?: (row: T) => ReactNode;
  align?: "left" | "center" | "right";
  hideOnMobile?: boolean;
}

export interface DataTableBulkAction {
  key: string;
  label: string;
  icon?: ReactNode;
  danger?: boolean;
  hidden?: boolean;
}

interface EmptyStateConfig {
  icon?: ReactNode;
  title: string;
  description: string;
  action?: ReactNode;
}

interface SchoolDataTableProps<T extends { id: string | number }> {
  data: T[];
  selectionData?: T[];
  columns: DataTableColumn<T>[];
  page: number;
  pageCount: number;
  pageSize: number;
  totalItems: number;
  onPageChange: (page: number) => void;
  searchQuery?: string;
  onSearchChange?: (query: string) => void;
  searchPlaceholder?: string;
  toolbar?: ReactNode;
  selectable?: boolean;
  selectedIds?: Set<T["id"]>;
  onSelectionChange?: (ids: Set<T["id"]>) => void;
  bulkActions?: DataTableBulkAction[];
  onBulkAction?: (actionKey: string, rows: T[]) => void;
  isLoading?: boolean;
  error?: string;
  onRetry?: () => void;
  emptyState: EmptyStateConfig;
  renderActions?: (row: T) => ReactNode;
  getRowLabel?: (row: T) => string;
}

const alignClass = (align?: "left" | "center" | "right") =>
  align === "right" ? "table-actions-heading" : align === "center" ? "table-cell-center" : "";

export function SchoolDataTable<T extends { id: string | number }>({
  data,
  selectionData,
  columns,
  page,
  pageCount,
  pageSize,
  totalItems,
  onPageChange,
  searchQuery,
  onSearchChange,
  searchPlaceholder = "Rechercher…",
  toolbar,
  selectable = false,
  selectedIds: controlledSelectedIds,
  onSelectionChange,
  bulkActions = [],
  onBulkAction,
  isLoading = false,
  error,
  onRetry,
  emptyState,
  renderActions,
  getRowLabel,
}: SchoolDataTableProps<T>) {
  const [internalSelectedIds, setInternalSelectedIds] = useState<Set<T["id"]>>(new Set());
  const [internalQuery, setInternalQuery] = useState("");
  const selectedIds = controlledSelectedIds ?? internalSelectedIds;
  const query = searchQuery ?? internalQuery;
  const allSelectionData = selectionData ?? data;
  const allSelected = data.length > 0 && data.every((row) => selectedIds.has(row.id));
  const someSelected = data.some((row) => selectedIds.has(row.id)) && !allSelected;
  const selectedRows = allSelectionData.filter((row) => selectedIds.has(row.id));
  const visibleBulkActions = bulkActions.filter((action) => !action.hidden);
  const columnCount = columns.length + (selectable ? 1 : 0) + (renderActions ? 1 : 0);

  const emitSelection = (next: Set<T["id"]>) => {
    if (!controlledSelectedIds) setInternalSelectedIds(next);
    onSelectionChange?.(next);
  };

  const toggleRow = (row: T) => {
    const next = new Set(selectedIds);
    if (next.has(row.id)) next.delete(row.id);
    else next.add(row.id);
    emitSelection(next);
  };

  const toggleCurrentPage = () => {
    const next = new Set(selectedIds);
    data.forEach((row) => {
      if (allSelected) next.delete(row.id);
      else next.add(row.id);
    });
    emitSelection(next);
  };

  return (
    <div className="lv-data-table">
      <div className="lv-data-table-header">
        <div className="lv-data-table-search">
          <Search size={16} aria-hidden="true" />
          <input
            type="search"
            value={query}
            onChange={(event) => {
              setInternalQuery(event.target.value);
              onSearchChange?.(event.target.value);
            }}
            placeholder={searchPlaceholder}
            aria-label={searchPlaceholder}
          />
        </div>
        {toolbar && <div className="lv-data-table-toolbar">{toolbar}</div>}
        {selectable && selectedIds.size > 0 && (
          <span className="lv-selection-count">{selectedIds.size} sélectionné{selectedIds.size > 1 ? "s" : ""}</span>
        )}
      </div>

      <div className="table-wrap">
        <table className="data-table lv-school-table">
          <thead>
            <tr>
              {selectable && (
                <th className="lv-table-select-cell">
                  <input
                    ref={(node) => { if (node) node.indeterminate = someSelected; }}
                    type="checkbox"
                    checked={allSelected}
                    onChange={toggleCurrentPage}
                    className="lv-table-checkbox"
                    aria-label="Sélectionner les lignes de cette page"
                  />
                </th>
              )}
              {columns.map((column, index) => (
                <th key={index} className={`${alignClass(column.align)} ${column.hideOnMobile ? "table-cell-mobile-hidden" : ""}`}>
                  {column.header}
                </th>
              ))}
              {renderActions && <th className="table-actions-heading">Actions</th>}
            </tr>
          </thead>
          <tbody>
            {isLoading ? (
              Array.from({ length: Math.min(pageSize, 6) }, (_, rowIndex) => (
                <tr className="lv-table-skeleton-row" key={rowIndex}>
                  {selectable && <td><span className="lv-skeleton lv-skeleton-checkbox" /></td>}
                  {columns.map((column, columnIndex) => (
                    <td key={columnIndex} className={column.hideOnMobile ? "table-cell-mobile-hidden" : ""}>
                      {columnIndex === 0 ? (
                        <div className="lv-skeleton-identity"><span className="lv-skeleton lv-skeleton-avatar" /><span><i className="lv-skeleton lv-skeleton-text-long" /><i className="lv-skeleton lv-skeleton-text-short" /></span></div>
                      ) : (
                        <span className={`lv-skeleton lv-skeleton-text ${column.align === "right" ? "lv-skeleton-right" : ""}`} />
                      )}
                    </td>
                  ))}
                  {renderActions && <td><span className="lv-skeleton lv-skeleton-action" /></td>}
                </tr>
              ))
            ) : error ? (
              <tr>
                <td colSpan={columnCount}>
                  <div className="lv-table-state" role="alert">
                    <span className="lv-table-state-icon lv-table-state-error"><AlertCircle size={25} /></span>
                    <div><h3>Impossible d’afficher les données</h3><p>{error}</p></div>
                    {onRetry && <button className="button button-secondary" type="button" onClick={onRetry}><RefreshCw size={15} /> Réessayer</button>}
                  </div>
                </td>
              </tr>
            ) : data.length === 0 ? (
              <tr>
                <td colSpan={columnCount}>
                  <div className="lv-table-state">
                    <span className="lv-table-state-icon">{emptyState.icon ?? <Search size={25} />}</span>
                    <div><h3>{emptyState.title}</h3><p>{emptyState.description}</p></div>
                    {emptyState.action}
                  </div>
                </td>
              </tr>
            ) : (
              data.map((row) => {
                const selected = selectable && selectedIds.has(row.id);
                return (
                  <tr key={row.id} className={selected ? "lv-table-row-selected" : ""}>
                    {selectable && (
                      <td className="lv-table-select-cell">
                        <input
                          type="checkbox"
                          checked={selected}
                          onChange={() => toggleRow(row)}
                          className="lv-table-checkbox"
                          aria-label={`Sélectionner ${getRowLabel?.(row) ?? String(row.id)}`}
                        />
                      </td>
                    )}
                    {columns.map((column, columnIndex) => (
                      <td key={columnIndex} className={`${alignClass(column.align)} ${column.hideOnMobile ? "table-cell-mobile-hidden" : ""}`}>
                        {column.render ? column.render(row) : column.key ? String(row[column.key] ?? "—") : null}
                      </td>
                    ))}
                    {renderActions && <td className="table-actions-heading">{renderActions(row)}</td>}
                  </tr>
                );
              })
            )}
          </tbody>
        </table>
      </div>

      {!isLoading && !error && data.length > 0 && (
        <TablePagination
          page={page}
          pageCount={pageCount}
          pageSize={pageSize}
          totalItems={totalItems}
          onPageChange={onPageChange}
          selectedCount={selectedIds.size}
        />
      )}

      {selectable && selectedIds.size > 0 && visibleBulkActions.length > 0 && (
        <div className="lv-bulk-bar" role="region" aria-label="Actions groupées">
          <div className="lv-bulk-bar-inner">
            <strong>{selectedIds.size} dossier{selectedIds.size > 1 ? "s" : ""} sélectionné{selectedIds.size > 1 ? "s" : ""}</strong>
            <span className="lv-bulk-divider" />
            <div className="lv-bulk-actions">
              {visibleBulkActions.map((action) => (
                <button
                  key={action.key}
                  type="button"
                  className={action.danger ? "lv-bulk-danger" : ""}
                  onClick={() => onBulkAction?.(action.key, selectedRows)}
                >
                  {action.icon ?? <Download size={15} />}
                  {action.label}
                </button>
              ))}
            </div>
            <span className="lv-bulk-divider" />
            <button className="lv-bulk-clear" type="button" onClick={() => emitSelection(new Set())}>
              <X size={14} /> Effacer
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
