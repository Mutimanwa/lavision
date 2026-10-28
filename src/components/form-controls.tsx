"use client";

import {
  forwardRef,
  useId,
  useEffect,
  useMemo,
  useRef,
  useState,
  type ChangeEvent,
  type InputHTMLAttributes,
  type KeyboardEvent,
  type ReactNode,
  type TextareaHTMLAttributes,
} from "react";
import { Check, ChevronDown, Eye, EyeOff, Search } from "lucide-react";

const cn = (...classes: Array<string | false | null | undefined>) => classes.filter(Boolean).join(" ");

type FieldSize = "sm" | "md" | "lg";

function FieldLabel({ id, label, required }: { id: string; label?: string; required?: boolean }) {
  if (!label) return null;
  return (
    <label className={cn("lv-field-label", required && "lv-field-required")} htmlFor={id}>
      {label}
    </label>
  );
}

function FieldMessage({ id, helperText, error }: { id: string; helperText?: string; error?: string }) {
  if (error) return <p className="lv-field-error" id={id} role="alert">{error}</p>;
  if (helperText) return <p className="lv-field-helper" id={id}>{helperText}</p>;
  return null;
}

export interface InputFormProps extends Omit<InputHTMLAttributes<HTMLInputElement>, "size"> {
  label?: string;
  helperText?: string;
  error?: string;
  prefixIcon?: ReactNode;
  suffixIcon?: ReactNode;
  size?: FieldSize;
  containerClassName?: string;
}

export const InputForm = forwardRef<HTMLInputElement, InputFormProps>(function InputForm(
  {
    label,
    helperText,
    error,
    prefixIcon,
    suffixIcon,
    size = "md",
    containerClassName,
    className,
    id,
    type = "text",
    required,
    disabled,
    readOnly,
    ...inputProps
  },
  ref,
) {
  const generatedId = useId();
  const inputId = id ?? generatedId;
  const messageId = `${inputId}-message`;
  const [showPassword, setShowPassword] = useState(false);
  const isPassword = type === "password";

  return (
    <div className={cn("lv-form-field", containerClassName)}>
      <FieldLabel id={inputId} label={label} required={required} />
      <div
        className={cn(
          "lv-input-container",
          `lv-input-${size}`,
          error && "lv-field-invalid",
          disabled && "lv-field-disabled",
          readOnly && "lv-field-readonly",
        )}
      >
        {prefixIcon && <span className="lv-input-icon" aria-hidden="true">{prefixIcon}</span>}
        <input
          {...inputProps}
          ref={ref}
          id={inputId}
          type={isPassword && showPassword ? "text" : type}
          className={cn("lv-input-control", className)}
          required={required}
          disabled={disabled}
          readOnly={readOnly}
          aria-invalid={Boolean(error)}
          aria-describedby={error || helperText ? messageId : undefined}
        />
        {isPassword ? (
          <button
            className="lv-input-action"
            type="button"
            onClick={() => setShowPassword((current) => !current)}
            aria-label={showPassword ? "Masquer le mot de passe" : "Afficher le mot de passe"}
          >
            {showPassword ? <EyeOff size={17} /> : <Eye size={17} />}
          </button>
        ) : suffixIcon ? (
          <span className="lv-input-icon" aria-hidden="true">{suffixIcon}</span>
        ) : null}
      </div>
      <FieldMessage id={messageId} helperText={helperText} error={error} />
    </div>
  );
});

export interface TextareaFormProps extends TextareaHTMLAttributes<HTMLTextAreaElement> {
  label?: string;
  helperText?: string;
  error?: string;
  showCount?: boolean;
  resizable?: boolean;
  containerClassName?: string;
}

export const TextareaForm = forwardRef<HTMLTextAreaElement, TextareaFormProps>(function TextareaForm(
  {
    label,
    helperText,
    error,
    showCount = false,
    resizable = false,
    containerClassName,
    className,
    id,
    required,
    disabled,
    readOnly,
    value,
    defaultValue,
    maxLength,
    onChange,
    ...textareaProps
  },
  ref,
) {
  const generatedId = useId();
  const inputId = id ?? generatedId;
  const messageId = `${inputId}-message`;
  const [uncontrolledLength, setUncontrolledLength] = useState(
    typeof defaultValue === "string" ? defaultValue.length : 0,
  );
  const currentLength = typeof value === "string" ? value.length : uncontrolledLength;

  return (
    <div className={cn("lv-form-field", containerClassName)}>
      {(label || showCount || maxLength !== undefined) && (
        <div className="lv-textarea-heading">
          <FieldLabel id={inputId} label={label} required={required} />
          {(showCount || maxLength !== undefined) && (
            <span className="lv-field-counter">
              {currentLength}{maxLength !== undefined ? ` / ${maxLength}` : ""}
            </span>
          )}
        </div>
      )}
      <textarea
        {...textareaProps}
        ref={ref}
        id={inputId}
        className={cn(
          "lv-textarea-control",
          resizable ? "lv-textarea-resizable" : "lv-textarea-fixed",
          error && "lv-field-invalid",
          disabled && "lv-field-disabled",
          className,
        )}
        required={required}
        disabled={disabled}
        readOnly={readOnly}
        value={value}
        defaultValue={defaultValue}
        maxLength={maxLength}
        onChange={(event) => {
          if (value === undefined) setUncontrolledLength(event.target.value.length);
          onChange?.(event);
        }}
        aria-invalid={Boolean(error)}
        aria-describedby={error || helperText ? messageId : undefined}
      />
      <FieldMessage id={messageId} helperText={helperText} error={error} />
    </div>
  );
});

export function sanitizeAmount(input: string, decimals: number): string {
  const maximumDecimals = Math.max(0, decimals);
  const cleaned = input.replace(/,/g, "").replace(/[^\d.]/g, "");
  const firstDot = cleaned.indexOf(".");
  if (firstDot === -1) return cleaned;
  const integer = cleaned.slice(0, firstDot);
  if (maximumDecimals === 0) return integer;
  const decimal = cleaned.slice(firstDot + 1).replace(/\./g, "").slice(0, maximumDecimals);
  return `${integer}.${decimal}`;
}

export function formatAmountThousands(raw: string): string {
  if (!raw) return "";
  const [integer, decimal] = raw.split(".");
  const grouped = integer.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  return decimal !== undefined ? `${grouped}.${decimal}` : grouped;
}

export interface AmountInputProps extends Omit<InputHTMLAttributes<HTMLInputElement>, "size" | "value" | "defaultValue" | "onChange" | "type"> {
  label?: string;
  helperText?: string;
  error?: string;
  currencySymbol?: string;
  currencyCode?: string;
  decimals?: number;
  size?: FieldSize;
  value?: string;
  defaultValue?: string;
  onValueChange?: (raw: string, parsed: number | null) => void;
  containerClassName?: string;
}

export const AmountInput = forwardRef<HTMLInputElement, AmountInputProps>(function AmountInput(
  {
    label,
    helperText,
    error,
    currencySymbol,
    currencyCode,
    decimals = 2,
    size = "md",
    value,
    defaultValue = "",
    onValueChange,
    containerClassName,
    className,
    id,
    required,
    disabled,
    readOnly,
    onFocus,
    onBlur,
    ...inputProps
  },
  ref,
) {
  const generatedId = useId();
  const inputId = id ?? generatedId;
  const messageId = `${inputId}-message`;
  const [internal, setInternal] = useState(defaultValue);
  const [focused, setFocused] = useState(false);
  const raw = value ?? internal;

  const updateValue = (event: ChangeEvent<HTMLInputElement>) => {
    const cleaned = sanitizeAmount(event.target.value, decimals);
    if (value === undefined) setInternal(cleaned);
    const parsed = cleaned === "" || cleaned === "." ? null : Number(cleaned);
    onValueChange?.(cleaned, parsed !== null && Number.isFinite(parsed) ? parsed : null);
  };

  return (
    <div className={cn("lv-form-field", containerClassName)}>
      <FieldLabel id={inputId} label={label} required={required} />
      <div className={cn("lv-input-container lv-amount-container", `lv-input-${size}`, error && "lv-field-invalid", disabled && "lv-field-disabled")}>
        {currencySymbol && <span className="lv-amount-symbol">{currencySymbol}</span>}
        <input
          {...inputProps}
          ref={ref}
          id={inputId}
          type="text"
          inputMode="decimal"
          className={cn("lv-input-control lv-amount-control", className)}
          value={focused ? raw : formatAmountThousands(raw)}
          required={required}
          disabled={disabled}
          readOnly={readOnly}
          onChange={updateValue}
          onFocus={(event) => { setFocused(true); onFocus?.(event); }}
          onBlur={(event) => { setFocused(false); onBlur?.(event); }}
          aria-invalid={Boolean(error)}
          aria-describedby={error || helperText ? messageId : undefined}
        />
        {currencyCode && <span className="lv-amount-code">{currencyCode}</span>}
      </div>
      <FieldMessage id={messageId} helperText={helperText} error={error} />
    </div>
  );
});

export interface SelectOption {
  value: string;
  label: string;
  description?: string;
  icon?: ReactNode;
  disabled?: boolean;
}

export interface SelectFormProps {
  label?: string;
  options: SelectOption[];
  value?: string;
  defaultValue?: string;
  onChange?: (value: string) => void;
  placeholder?: string;
  helperText?: string;
  error?: string;
  prefixIcon?: ReactNode;
  size?: FieldSize;
  name?: string;
  id?: string;
  required?: boolean;
  disabled?: boolean;
  readOnly?: boolean;
  dropdownLabel?: string;
  containerClassName?: string;
  className?: string;
  ariaLabel?: string;
}

interface SelectFieldProps extends SelectFormProps {
  searchable?: boolean;
  searchPlaceholder?: string;
  filter?: boolean;
}

function SelectField({
  label,
  options,
  value,
  defaultValue = "",
  onChange,
  placeholder = "Sélectionner…",
  helperText,
  error,
  prefixIcon,
  size = "md",
  name,
  id,
  required,
  disabled,
  readOnly,
  dropdownLabel,
  containerClassName,
  className,
  ariaLabel,
  searchable = false,
  searchPlaceholder = "Rechercher…",
  filter = false,
}: SelectFieldProps) {
  const generatedId = useId();
  const fieldId = id ?? generatedId;
  const listboxId = `${fieldId}-listbox`;
  const messageId = `${fieldId}-message`;
  const rootRef = useRef<HTMLDivElement>(null);
  const buttonRef = useRef<HTMLButtonElement>(null);
  const searchRef = useRef<HTMLInputElement>(null);
  const [open, setOpen] = useState(false);
  const [internalValue, setInternalValue] = useState(defaultValue);
  const [query, setQuery] = useState("");
  const [highlighted, setHighlighted] = useState(-1);
  const currentValue = value ?? internalValue;
  const selected = options.find((option) => option.value === currentValue);
  const filteredOptions = useMemo(() => {
    if (!searchable || !query.trim()) return options;
    const normalized = query.trim().toLocaleLowerCase("fr");
    return options.filter((option) =>
      `${option.label} ${option.description ?? ""}`.toLocaleLowerCase("fr").includes(normalized),
    );
  }, [options, query, searchable]);

  useEffect(() => {
    if (!open) return;
    const closeOutside = (event: MouseEvent) => {
      if (rootRef.current && !rootRef.current.contains(event.target as Node)) setOpen(false);
    };
    document.addEventListener("mousedown", closeOutside);
    return () => document.removeEventListener("mousedown", closeOutside);
  }, [open]);

  const firstEnabledIndex = (items: SelectOption[]) => items.findIndex((option) => !option.disabled);

  const openDropdown = () => {
    if (disabled || readOnly) return;
    const selectedIndex = options.findIndex((option) => option.value === currentValue && !option.disabled);
    setHighlighted(selectedIndex >= 0 ? selectedIndex : firstEnabledIndex(options));
    setQuery("");
    setOpen(true);
    if (searchable) requestAnimationFrame(() => searchRef.current?.focus());
  };

  const closeDropdown = (restoreFocus = false) => {
    setOpen(false);
    setQuery("");
    if (restoreFocus) buttonRef.current?.focus();
  };

  const selectOption = (option: SelectOption) => {
    if (option.disabled) return;
    setInternalValue(option.value);
    onChange?.(option.value);
    closeDropdown(true);
  };

  const moveHighlight = (direction: 1 | -1) => {
    if (!filteredOptions.length) return;
    let next = highlighted;
    for (let count = 0; count < filteredOptions.length; count += 1) {
      next = (next + direction + filteredOptions.length) % filteredOptions.length;
      if (!filteredOptions[next]?.disabled) break;
    }
    setHighlighted(next);
  };

  const handleKeyDown = (event: KeyboardEvent<HTMLElement>, fromSearch = false) => {
    if (disabled || readOnly) return;
    if (fromSearch && event.key === " ") return;
    switch (event.key) {
      case "ArrowDown":
      case "ArrowUp": {
        event.preventDefault();
        if (!open) openDropdown();
        else moveHighlight(event.key === "ArrowDown" ? 1 : -1);
        break;
      }
      case "Enter": {
        event.preventDefault();
        if (!open) openDropdown();
        else if (highlighted >= 0 && filteredOptions[highlighted]) selectOption(filteredOptions[highlighted]);
        break;
      }
      case " ": {
        event.preventDefault();
        if (open && highlighted >= 0 && filteredOptions[highlighted]) selectOption(filteredOptions[highlighted]);
        else if (!open) openDropdown();
        break;
      }
      case "Escape":
        if (open) {
          event.preventDefault();
          closeDropdown(true);
        }
        break;
      case "Tab":
        if (open) closeDropdown();
        break;
      default:
        break;
    }
  };

  return (
    <div className={cn("lv-form-field", filter && "lv-filter-field", containerClassName)} ref={rootRef}>
      <FieldLabel id={fieldId} label={label} required={required} />
      {name && <input type="hidden" name={name} value={currentValue} required={required} readOnly />}
      <div className="lv-select-root">
        <button
          ref={buttonRef}
          id={fieldId}
          type="button"
          className={cn(
            "lv-select-trigger",
            `lv-input-${size}`,
            open && "lv-select-open",
            error && "lv-field-invalid",
            (disabled || readOnly) && "lv-field-disabled",
            className,
          )}
          onClick={() => open ? closeDropdown() : openDropdown()}
          onKeyDown={(event) => handleKeyDown(event)}
          aria-label={ariaLabel ?? label}
          aria-haspopup="listbox"
          aria-expanded={open}
          aria-controls={listboxId}
          aria-activedescendant={open && highlighted >= 0 ? `${fieldId}-option-${highlighted}` : undefined}
          aria-describedby={error || helperText ? messageId : undefined}
          disabled={disabled}
        >
          {prefixIcon && <span className="lv-input-icon" aria-hidden="true">{prefixIcon}</span>}
          {selected?.icon && <span className="lv-select-option-icon" aria-hidden="true">{selected.icon}</span>}
          <span className={cn("lv-select-value", !selected && "lv-select-placeholder")}>
            {selected?.label ?? placeholder}
          </span>
          <ChevronDown className={cn("lv-select-chevron", open && "lv-select-chevron-open")} size={16} />
        </button>

        {open && (
          <div className="lv-select-dropdown" id={listboxId} role="listbox" aria-label={dropdownLabel ?? label ?? placeholder}>
            {searchable && (
              <div className="lv-select-search">
                <Search size={15} aria-hidden="true" />
                <input
                  ref={searchRef}
                  type="search"
                  value={query}
                  onChange={(event) => {
                    const nextQuery = event.target.value;
                    setQuery(nextQuery);
                    const normalized = nextQuery.trim().toLocaleLowerCase("fr");
                    const nextOptions = options.filter((option) =>
                      !normalized || `${option.label} ${option.description ?? ""}`.toLocaleLowerCase("fr").includes(normalized),
                    );
                    setHighlighted(firstEnabledIndex(nextOptions));
                  }}
                  onKeyDown={(event) => handleKeyDown(event, true)}
                  placeholder={searchPlaceholder}
                  aria-label={searchPlaceholder}
                />
              </div>
            )}
            {dropdownLabel && <p className="lv-select-dropdown-label">{dropdownLabel}</p>}
            <div className="lv-select-options">
              {filteredOptions.map((option, index) => {
                const active = option.value === currentValue;
                return (
                  <button
                    key={option.value}
                    id={`${fieldId}-option-${index}`}
                    type="button"
                    role="option"
                    aria-selected={active}
                    disabled={option.disabled}
                    tabIndex={-1}
                    className={cn(
                      "lv-select-option",
                      active && "lv-select-option-active",
                      highlighted === index && "lv-select-option-highlighted",
                    )}
                    onMouseEnter={() => setHighlighted(index)}
                    onClick={() => selectOption(option)}
                  >
                    {option.icon && <span className="lv-select-option-icon" aria-hidden="true">{option.icon}</span>}
                    <span className="lv-select-option-copy">
                      <span>{option.label}</span>
                      {option.description && <small>{option.description}</small>}
                    </span>
                    {active && <Check size={15} />}
                  </button>
                );
              })}
              {filteredOptions.length === 0 && (
                <p className="lv-select-empty">{query ? `Aucun résultat pour « ${query} »` : "Aucune option disponible"}</p>
              )}
            </div>
          </div>
        )}
      </div>
      <FieldMessage id={messageId} helperText={helperText} error={error} />
    </div>
  );
}

export function SelectForm(props: SelectFormProps) {
  return <SelectField {...props} />;
}

export function SearchSelectForm({ searchPlaceholder, ...props }: SelectFormProps & { searchPlaceholder?: string }) {
  return <SelectField {...props} searchable searchPlaceholder={searchPlaceholder} />;
}

export function FilterSelect({
  options,
  value,
  onChange,
  placeholder = "Filtrer…",
  ariaLabel,
  className,
}: Pick<SelectFormProps, "options" | "value" | "onChange" | "placeholder" | "ariaLabel" | "className">) {
  return (
    <SelectField
      options={options}
      value={value}
      onChange={onChange}
      placeholder={placeholder}
      ariaLabel={ariaLabel}
      className={className}
      size="sm"
      filter
    />
  );
}

export function CheckboxForm({
  label,
  description,
  id,
  name,
  checked,
  defaultChecked = false,
  onChange,
  disabled,
  readOnly,
  required,
  error,
  indeterminate = false,
  ariaLabel,
}: {
  label?: ReactNode;
  description?: string;
  id?: string;
  name?: string;
  checked?: boolean;
  defaultChecked?: boolean;
  onChange?: (checked: boolean) => void;
  disabled?: boolean;
  readOnly?: boolean;
  required?: boolean;
  error?: string;
  indeterminate?: boolean;
  ariaLabel?: string;
}) {
  const generatedId = useId();
  const inputId = id ?? generatedId;
  const [internalChecked, setInternalChecked] = useState(defaultChecked);
  const isChecked = checked ?? internalChecked;

  return (
    <div className="lv-checkbox-field">
      <label htmlFor={inputId} className={cn("lv-checkbox-label", (disabled || readOnly) && "lv-field-disabled")}>
        <span className="lv-checkbox-box-wrap">
          <input
            ref={(node) => { if (node) node.indeterminate = indeterminate; }}
            id={inputId}
            name={name}
            type="checkbox"
            checked={isChecked}
            disabled={disabled}
            readOnly={readOnly}
            required={required}
            aria-label={ariaLabel}
            aria-invalid={Boolean(error)}
            onChange={(event) => {
              if (readOnly) return;
              if (checked === undefined) setInternalChecked(event.target.checked);
              onChange?.(event.target.checked);
            }}
          />
          <span className={cn("lv-checkbox-box", (isChecked || indeterminate) && "lv-checkbox-checked")} aria-hidden="true">
            {indeterminate && !isChecked ? <span className="lv-checkbox-indeterminate" /> : <Check size={13} />}
          </span>
        </span>
        {(label || description) && (
          <span className="lv-checkbox-copy">
            {label && <span>{label}</span>}
            {description && <small>{description}</small>}
          </span>
        )}
      </label>
      {error && <p className="lv-field-error" role="alert">{error}</p>}
    </div>
  );
}
