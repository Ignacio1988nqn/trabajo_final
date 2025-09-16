<template>
  <div class="counter-container">
    <h2>Contador Vue</h2>
    <p>Conteo: {{ count }}</p>
    <button @click="increment" class="btn btn-primary">Incrementar</button>
    <button @click="decrement" class="btn btn-secondary">Decrementar</button>
  </div>
</template>

<script>
export default {
  name: 'Counter',
  data() {
    return {
      count: 0
    }
  },
  methods: {
    increment() {
      this.count++;
    },
    decrement() {
      this.count--;
    }
  }
}
</script>

<style scoped>
.counter-container {
  text-align: center;
  padding: 20px;
  max-width: 300px;
  margin: 0 auto;
}

.btn {
  padding: 8px 16px;
  margin: 5px;
  border-radius: 4px;
  border: none;
  cursor: pointer;
}

.btn-primary {
  background-color: #4CAF50;
  color: white;
}

.btn-secondary {
  background-color: #f44336;
  color: white;
}
</style>