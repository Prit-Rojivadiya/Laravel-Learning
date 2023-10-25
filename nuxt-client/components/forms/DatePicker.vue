<template>
  <v-menu
    v-model="menu"
    :close-on-content-click="false"
    transition="scale-transition"
    offset-y
    min-width="auto"
  >
    <template v-slot:activator="{ on, attrs }">
      <v-text-field
        v-model="dateParsed"
        v-bind="$attrs"
        :rules="mergedRules"
        readonly
        clearable
        v-on="on"
        :v-bind="$attrs"
      />
    </template>
    <v-date-picker
      :value="value"
      no-title
      scrollable
      @input="handleInput"
    >
    </v-date-picker>
  </v-menu>
</template>

<script>
import ParsesDates from '@/components/mixin/ParsesDates'

export default {
  props: ['value', 'rules','require_date'],
  inject: ['form'],
  mixins: [ParsesDates],
  data: () => ({
    menu: false,
    dateParsed: null,
  }),
  watch: {
    dateParsed (newVal, oldVal) {
      //handle "clearable event"
      if (oldVal && !newVal) {
        this.handleInput(newVal)
      }
    },
    value: {
      handler (val) {
        if (val) {
          this.dateParsed = this.formatDate(val)
        }
        else {
          this.dateParsed = val
        }
      }
    },
  },
  computed: {
    mergedRules () {
      let rules = this.rules ? this.rules : [];
      if(this.require_date) {
        rules.push((v) => {
            return !!v || 'Please select a date'
          }
        )
      }
      return rules
    }
  },
  created () {
    this.dateParsed = this.formatDate(this.value)
  },
  methods: {
    handleInput(value) {
      this.menu = false
      this.$emit('input', value)
    }
  }
}
</script>
