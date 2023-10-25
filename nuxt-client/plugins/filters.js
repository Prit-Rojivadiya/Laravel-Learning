import Vue from 'vue'
import moment from 'moment'

Vue.filter('formatDate', function (value) {
  if (value) {
    return moment(String(value)).format('dddd LL')
  }
})

Vue.filter('formatDateMDY', function (value) {
  if (value && !Array.isArray(value)) {
    return moment(String(value)).format('MM/DD/YYYY')
  }
})

Vue.filter('formatDateMDYhhmmss', function (value) {
  if (value && !Array.isArray(value)) {
    return moment(String(value)).format('MM/DD/YYYY HH:mm:ss')
  }
})

Vue.filter('formatMoney', function(value) {
  if (!value) {
    return '$0.00'
  }
  return '$'+parseFloat(value).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits:2})
})

Vue.filter('formatMoneyNoSign', function(value) {
  if (!value) {
    return '0.00'
  }
  return parseFloat(value).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits:2})
})

Vue.filter('formatNumber', function(value) {
  if (!value) {
    return '0'
  }
  return parseFloat(value).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits:2})
})

Vue.filter('formatBoolean', function(value) {
  if (!value || value === 0) {
    return 'false'
  }
  else {
    return 'true'
  }
})

Vue.filter('formatYesNo', function(value) {
  if (!value || value === 0) {
    return 'No'
  }
  else {
    return 'Yes'
  }
})

Vue.filter('formatCapitalize', function(value) {
  if (value && !Array.isArray(value)) {
    return value.toLowerCase().replace( /\b./g, function(a){ return a.toUpperCase(); } )
  }
})

Vue.filter('formatSplitWordsByCapitalize', function(value) {
  if (value && !Array.isArray(value)) {
    return value.match(/[A-Z][a-z]+|[0-9]+/g).join(" ")
  }
})

import { VueMaskDirective } from 'v-mask'
Vue.directive('mask', VueMaskDirective);
