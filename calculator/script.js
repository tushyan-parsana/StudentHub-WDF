/**
 * Slim & Stylish Calculator — Vanilla JS (DOM only)
 * No frameworks, no eval(). Pure arithmetic logic.
 */

(function () {
  'use strict';

  // ── DOM References ──────────────────────────────────
  const display      = document.getElementById('calcResult');
  const expression   = document.getElementById('calcExpression');
  const keysContainer = document.getElementById('calcKeys');

  // ── State ───────────────────────────────────────────
  let currentValue   = '0';
  let previousValue  = '';
  let operator       = null;
  let shouldReset    = false;
  let lastEquals     = false;

  // ── Helpers ─────────────────────────────────────────
  const MAX_DIGITS = 15;

  /** Format a number for display (trim trailing zeros, limit length) */
  function formatNumber(num) {
    if (num === 'Error') return 'Error';
    const n = parseFloat(num);
    if (isNaN(n)) return 'Error';
    if (!isFinite(n)) return 'Error';

    let str = n.toString();

    // Switch to exponential for very large / small numbers
    if (str.length > MAX_DIGITS) {
      str = n.toPrecision(8);
    }
    return str;
  }

  /** Update the DOM display */
  function updateDisplay() {
    display.textContent = formatNumber(currentValue);

    // Shrink text for long values
    display.classList.toggle('shrink', display.textContent.length > 10);
  }

  /** Build the expression string shown above the result */
  function updateExpression() {
    if (operator && previousValue !== '') {
      const opSymbol = { '/': '÷', '*': '×', '-': '−', '+': '+' }[operator] || operator;
      expression.textContent = `${formatNumber(previousValue)} ${opSymbol}`;
    } else {
      expression.textContent = '';
    }
  }

  /** Flash animation on a key */
  function flashKey(btn) {
    btn.classList.remove('flash');
    // Force reflow so the class re-triggers
    void btn.offsetWidth;
    btn.classList.add('flash');
    setTimeout(() => btn.classList.remove('flash'), 300);
  }

  // ── Core Actions ────────────────────────────────────

  function inputNumber(value) {
    if (lastEquals) {
      // After pressing =, start fresh with the new number
      currentValue = value;
      previousValue = '';
      operator = null;
      lastEquals = false;
      updateExpression();
      updateDisplay();
      return;
    }

    if (shouldReset) {
      currentValue = value;
      shouldReset = false;
    } else {
      if (currentValue === '0' && value !== '.') {
        currentValue = value;
      } else {
        if (currentValue.replace('-', '').replace('.', '').length >= MAX_DIGITS) return;
        currentValue += value;
      }
    }
    updateDisplay();
  }

  function inputDecimal() {
    if (lastEquals) {
      currentValue = '0.';
      previousValue = '';
      operator = null;
      lastEquals = false;
      updateExpression();
      updateDisplay();
      return;
    }

    if (shouldReset) {
      currentValue = '0.';
      shouldReset = false;
      updateDisplay();
      return;
    }
    if (currentValue.includes('.')) return;
    currentValue += '.';
    updateDisplay();
  }

  function inputOperator(op) {
    lastEquals = false;

    if (operator && !shouldReset) {
      // Chain calculation
      calculate();
    }

    previousValue = currentValue;
    operator = op;
    shouldReset = true;

    // Highlight the active operator
    highlightOperator(op);
    updateExpression();
  }

  function calculate() {
    if (!operator || previousValue === '') return;

    const a = parseFloat(previousValue);
    const b = parseFloat(currentValue);
    let result;

    switch (operator) {
      case '+': result = a + b; break;
      case '-': result = a - b; break;
      case '*': result = a * b; break;
      case '/': result = b === 0 ? 'Error' : a / b; break;
      default:  return;
    }

    // Fix floating-point quirks
    if (result !== 'Error') {
      result = parseFloat(result.toPrecision(12));
    }

    currentValue = result.toString();
    expression.textContent = `${formatNumber(previousValue)} ${{ '/': '÷', '*': '×', '-': '−', '+': '+' }[operator]} ${formatNumber(b)} =`;
    previousValue = '';
    operator = null;
    shouldReset = true;
    lastEquals = true;

    clearOperatorHighlight();
    updateDisplay();
  }

  function clearAll() {
    currentValue  = '0';
    previousValue = '';
    operator      = null;
    shouldReset   = false;
    lastEquals    = false;
    clearOperatorHighlight();
    updateExpression();
    updateDisplay();
  }

  function toggleSign() {
    if (currentValue === '0' || currentValue === 'Error') return;
    currentValue = currentValue.startsWith('-')
      ? currentValue.slice(1)
      : '-' + currentValue;
    updateDisplay();
  }

  function percent() {
    if (currentValue === 'Error') return;
    currentValue = (parseFloat(currentValue) / 100).toString();
    updateDisplay();
  }

  // ── Operator Highlight ──────────────────────────────
  function highlightOperator(op) {
    clearOperatorHighlight();
    const map = { '/': 'keyDivide', '*': 'keyMultiply', '-': 'keySubtract', '+': 'keyAdd' };
    const btn = document.getElementById(map[op]);
    if (btn) btn.classList.add('active');
  }

  function clearOperatorHighlight() {
    document.querySelectorAll('.key--op').forEach(b => b.classList.remove('active'));
  }

  // ── Event Delegation (DOM) ──────────────────────────
  keysContainer.addEventListener('click', function (e) {
    const btn = e.target.closest('.key');
    if (!btn) return;

    flashKey(btn);

    const action = btn.dataset.action;
    const value  = btn.dataset.value;

    switch (action) {
      case 'number':   inputNumber(value); break;
      case 'decimal':  inputDecimal();     break;
      case 'operator': inputOperator(value); break;
      case 'equals':   calculate();        break;
      case 'clear':    clearAll();         break;
      case 'toggle':   toggleSign();       break;
      case 'percent':  percent();          break;
    }
  });

  // ── Keyboard Support ────────────────────────────────
  document.addEventListener('keydown', function (e) {
    const key = e.key;

    if (key >= '0' && key <= '9') {
      inputNumber(key);
      flashById('key' + key);
    } else if (key === '.') {
      inputDecimal();
      flashById('keyDot');
    } else if (key === '+') {
      inputOperator('+');
      flashById('keyAdd');
    } else if (key === '-') {
      inputOperator('-');
      flashById('keySubtract');
    } else if (key === '*') {
      inputOperator('*');
      flashById('keyMultiply');
    } else if (key === '/') {
      e.preventDefault(); // prevent browser search
      inputOperator('/');
      flashById('keyDivide');
    } else if (key === 'Enter' || key === '=') {
      calculate();
      flashById('keyEquals');
    } else if (key === 'Escape' || key === 'Delete') {
      clearAll();
      flashById('keyClear');
    } else if (key === '%') {
      percent();
      flashById('keyPercent');
    } else if (key === 'Backspace') {
      backspace();
    }
  });

  function flashById(id) {
    const btn = document.getElementById(id);
    if (btn) flashKey(btn);
  }

  function backspace() {
    if (shouldReset || lastEquals || currentValue === 'Error') {
      clearAll();
      return;
    }
    currentValue = currentValue.length > 1
      ? currentValue.slice(0, -1)
      : '0';
    updateDisplay();
  }

  // ── Initial Render ──────────────────────────────────
  updateDisplay();
})();
