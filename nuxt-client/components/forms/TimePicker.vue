<template>
  <div class="d-flex">
    <v-text-field
      style="width: 35px"
      dense
      :label="label"
      v-mask="'##'"
      v-model="hour"
      @change="onValueChange"
    />
    &nbsp;&nbsp;<span class="d-flex align-center">:</span>&nbsp;&nbsp;
    <v-text-field
      style="width: 35px"
      dense
      v-model="minute"
      v-mask="'##'"
      @change="onValueChange"
    />
    &nbsp;&nbsp;
    <v-select
      v-model="amPm"
      dense
      :items="['AM', 'PM']"
      style="width: 60px"
      @change="onValueChange"
    />
  </div>
</template>

<script>
import VCleaveInput from '@/components/VCleaveInput'

export default {
  props: ['value', 'label'],
  components: {
    VCleaveInput
  },
  data () {
    return {
      local: this.value,
      hour: null,
      minute: null,
      amPm: null,
    }
  },
  created () {
    var [time, amPm] = this.local.split(' ');
    this.amPm = amPm

    var [hour, minute] = time.split(':')
    this.hour = hour
    this.minute = minute
  },
  methods: {
    onValueChange (value) {
      if (this.minute.length == 1) {
        this.minute = 0 + this.minute
      }

      this.$emit('input', this.hour + ':' + this.minute + ' ' + this.amPm)
    }
  }
}
</script>
