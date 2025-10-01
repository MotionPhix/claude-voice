import { ref, computed } from 'vue'

export interface Currency {
  code: string
  name: string
  symbol: string
  exchange_rate: number
  is_base: boolean
  is_active: boolean
}

export const useCurrency = () => {
  // Format currency amount
  const formatCurrency = (amount: number, currencyCode: string = 'USD'): string => {
    try {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currencyCode,
      }).format(amount)
    } catch {
      // Fallback formatting if currency code is not supported
      return `${amount.toFixed(2)} ${currencyCode}`
    }
  }

  // Format amount with symbol
  const formatAmount = (amount: number, symbol: string = '$'): string => {
    return `${symbol}${new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(amount)}`
  }

  // Format exchange rate
  const formatExchangeRate = (rate: number): string => {
    return new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 6,
    }).format(rate)
  }

  // Get currency symbol from code
  const getCurrencySymbol = (currencies: Currency[], code: string): string => {
    const currency = currencies.find(c => c.code === code)
    return currency?.symbol || '$'
  }

  // Convert between currencies (client-side approximation)
  const convertCurrency = (
    amount: number,
    fromCurrency: Currency,
    toCurrency: Currency
  ): number => {
    if (fromCurrency.code === toCurrency.code) {
      return amount
    }

    // Convert to base currency first
    const baseAmount = fromCurrency.is_base
      ? amount
      : amount / fromCurrency.exchange_rate

    // Convert from base to target currency
    return toCurrency.is_base
      ? baseAmount
      : baseAmount * toCurrency.exchange_rate
  }

  // Get popular currencies for quick selection
  const getPopularCurrencies = (currencies: Currency[]): Currency[] => {
    const popularCodes = ['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'CNY']
    return currencies
      .filter(c => popularCodes.includes(c.code) && c.is_active)
      .sort((a, b) => {
        const aIndex = popularCodes.indexOf(a.code)
        const bIndex = popularCodes.indexOf(b.code)
        return aIndex - bIndex
      })
  }

  // Format currency for display in lists
  const formatCurrencyOption = (currency: Currency): string => {
    return `${currency.code} - ${currency.name}`
  }

  // Validate currency code format
  const isValidCurrencyCode = (code: string): boolean => {
    return /^[A-Z]{3}$/.test(code)
  }

  return {
    formatCurrency,
    formatAmount,
    formatExchangeRate,
    getCurrencySymbol,
    convertCurrency,
    getPopularCurrencies,
    formatCurrencyOption,
    isValidCurrencyCode,
  }
}
