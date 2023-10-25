<template>
  <v-text-field
    :value="value"
    ref="field"
    label="Email"
    @input="updateValue"
    required
    dense
    :rules="rules"
    v-bind="$attrs"
  />
</template>

<script>
  export default {
    inject: ['form'],
    props: {
      value: {
        type: String
      },
      required: {
        type: Boolean,
        default: false
      }
    },
    // data: () => ({
    //   rules: [
    //     (v) => {
    //       if (!v && this.required) {
    //         return 'Please enter a value'
    //       }
    //
    //       return this.validateEmail(v) || 'Please enter a valid email'
    //     }
    //   ]
    // }),
    computed: {
      rules () {
        return [
          (v) => {
            if (!v && this.required) {
              return 'Please enter a value'
            }

            if (!v) {
              return true
            }

            return this.validateEmail(v) || 'Please enter a valid email'
          }
        ]
      }
    },
    created: function created () {
      this.form && this.form.register(this)
    },
    methods: {
       validateEmail(email) {
        const re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

        return re.test(email);
      },
      updateValue (value) {
        this.$emit('input', value)
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
  }
</script>
