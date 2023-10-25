<template>
  <v-select
    :value="value"
    :items="stateOptions"
    dense
    @input="updateValue"
    v-bind="$attrs"
  />
</template>

<script>
export default {
  props: {
    value: {
      type: String
    }
  },
  data: () => ({
    stateOptions: [
      { 'text': 'Alaska', 'value':      'alaska' },
      { 'text': 'Alabama', 'value':     'alabama' },
      { 'text': 'Arizona', 'value':     'arizona'  },
      { 'text': 'Arkansas', 'value':    'arkansas'  },
      { 'text': 'California', 'value':  'california' },
      { 'text': 'Colorado', 'value':    'colorado' },
      { 'text': 'Connecticut', 'value': 'connecticut' },
      { 'text': 'Delaware', 'value':    'delaware' },
      { 'text': 'Florida', 'value':     'florida' },
      { 'text': 'Georgia', 'value':     'georgia'},
      { 'text': 'Hawaii', 'value':      'hawaii'},
      { 'text': 'Idaho', 'value':       'idaho'},
      { 'text': 'Illinois', 'value':    'illinois'},
      { 'text': 'Indiana', 'value':     'indiana'},
      { 'text': 'Iowa', 'value':        'iowa'},
      { 'text': 'Kansas', 'value':      'kansas'},
      { 'text': 'Kentucky', 'value':    'kentucky'},
      { 'text': 'Louisiana', 'value':   'louisiana'},
      { 'text': 'Maine', 'value':       'maine'},
      { 'text': 'Maryland', 'value':    'maryland'},
      { 'text': 'Massachusetts', 'value': 'massachusetts'},
      { 'text': 'Michigan', 'value':    'michigan'},
      { 'text': 'Minnesota', 'value':   'minnesota'},
      { 'text': 'Mississippi', 'value': 'mississippi'},
      { 'text': 'Missouri', 'value':    'missouri'},
      { 'text': 'Montana', 'value':     'montana'},
      { 'text': 'Nebraska', 'value':    'nebraska'},
      { 'text': 'Nevada', 'value':      'nevada'},
      { 'text': 'New Hampshire', 'value': 'new hampshire'},
      { 'text': 'New Jersey', 'value':  'new jersey'},
      { 'text': 'New Mexico', 'value':  'new mexico'},
      { 'text': 'New York', 'value':    'new york'},
      { 'text': 'North Carolina', 'value': 'north carolina'},
      { 'text': 'North Dakota', 'value': 'north dakota'},
      { 'text': 'Ohio', 'value':        'ohio'},
      { 'text': 'Oklahoma', 'value':    'oklahoma'},
      { 'text': 'Oregon', 'value':      'oregon'},
      { 'text': 'Pennsylvania', 'value': 'pennsylvania'},
      { 'text': 'Rhode Island', 'value': 'rhode island'},
      { 'text': 'South Carolina', 'value': 'south carolina'},
      { 'text': 'South Dakota', 'value': 'south dakota'},
      { 'text': 'Tennessee', 'value':   'tennessee'},
      { 'text': 'Texas', 'value':       'texas'},
      { 'text': 'Utah', 'value':        'utah'},
      { 'text': 'Vermont', 'value':     'vermont'},
      { 'text': 'Virginia', 'value':    'virginia'},
      { 'text': 'Washington', 'value':  'washington'},
      { 'text': 'West Virginia', 'value': 'west virginia'},
      { 'text': 'Wisconsin', 'value':   'wisconsin'},
      { 'text': 'Wyoming', 'value':     'wyoming' }
    ]
  }),
  methods: {
    updateValue (value) {
      this.$emit('input', value)
    }
  }
}
</script>
