import React from 'react';

export default function NewTest() {
  return (
    <div style={{
      padding: '2rem',
      backgroundColor: '#ff0000',
      color: 'white',
      fontSize: '3rem',
      height: '100vh',
      display: 'flex',
      flexDirection: 'column',
      justifyContent: 'center',
      alignItems: 'center'
    }}>
      <h1 style={{ marginBottom: '2rem' }}>NEW TEST COMPONENT</h1>
      <p>This is a highly visible new test component</p>
      <p style={{ marginTop: '2rem' }}>If you can see this, rendering is working!</p>
    </div>
  );
}
