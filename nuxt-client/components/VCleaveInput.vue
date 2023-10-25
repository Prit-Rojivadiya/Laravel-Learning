<script>
  import Cleave from "cleave.js";
  export default {
    props: {
      value: {
        default: null,
        required: true,
        validator(value) {
          return value === null || typeof value === 'string' || value instanceof String || typeof value === 'number'
        }
      },
      // https://github.com/nosir/cleave.js/blob/master/doc/options.md
      options: {
        type: Object,
        default: () => ({})
      },
      // Set this prop as `false` to emit masked value
      raw: {
        type: Boolean,
        default: true
      },
      tabIndex: {
        type: Number,
        default: -1
      },
    },
    inject: ['form'],
    data() {
      return {
        local: this.value,
        rawValue: null,
        rules: {
          required: value => !!value || 'Required.',
          requiredPhone: value => {
            return 'Fail'
          },
        },
      };
    },
    created: function created () {
      this.form && this.form.register(this)
    },
    mounted() {
      this.cleave = new Cleave(this.$el.querySelector("input"), this.getOptions(this.options));
      // this.cleave.setRawValue(this.value);
    },
    beforeUpdate() {
      // to allow the first character to be seen we need to do this conditionally - https://sssf.teamwork.com/desk/tickets/5482984/messages
      if (this.value) {
        this.cleave.setRawValue(this.value);
      }
    },
    methods: {
      /**
       * Inject our method in config options
       *
       * @param options Object
       */
      getOptions(options) {
        // Preserve original callback
        this.onValueChangedFn = options.onValueChanged;

        return Object.assign({}, options, {
          onValueChanged: this.onValueChanged
        });
      },
      /**
       * Watch for value changed by cleave and notify parent component
       *
       * @param event
       */
      onValueChanged(event) {
        let value = this.raw ? event.target.rawValue : event.target.value;
        this.$emit('input', value);

        // Call original callback method
        if (typeof this.onValueChangedFn === 'function') {
          this.onValueChangedFn.call(this, event)
        }
      },
      validate () {
        return this.$refs.field.validate()
      },
      reset () {
        return this.$refs.field.reset()
      },
      resetValidation () {
        return this.$refs.field.resetValidation()
      },
    }
  };
</script>

<template>
  <v-text-field
    ref="field"
    :value="local"
    v-bind="$attrs"
    @blur="$emit('blur', rawValue)"
    :tabindex="tabIndex"
  />
</template>
