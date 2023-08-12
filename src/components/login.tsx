import React, { FormEvent, useEffect, useReducer } from "react";

import Button from 'react-bootstrap/Button';
import Form from 'react-bootstrap/Form';
import { appReducer, appState } from "../state/reducers";
import { useCustomContext } from "../state/custom.context";
import { useNavigate } from "react-router-dom";

export function Login() {
  const {state, dispatch} = useCustomContext();
  const {userName, loginState} = state;

  const navigate = useNavigate();
  useEffect(()=>{
    if (loginState && userName)
      navigate('/groups')
  }, [loginState, userName])

  const login = (event: FormEvent)=>{
    event.preventDefault();
    const email = document.getElementById('email') as HTMLInputElement;
    dispatch({type:'setLoginState', payload:true});
    dispatch({type:'setUserName', payload: email.value});
  }


  return (
    <Form onSubmit={login}>
      <Form.Group className="mb-3" controlId="formBasicEmail">
        <Form.Label>Email</Form.Label>
        <Form.Control type="email" placeholder="Epost" id='email' />
        <Form.Text className="text-muted">
          Fyll i din e-post för att identifiera dig.
        </Form.Text>
      </Form.Group>

      <Form.Group className="mb-3" controlId="formBasicPassword">
        <Form.Label>Lösenord</Form.Label>
        <Form.Control type="password" placeholder="Lösenord" id='password'/>
      </Form.Group>
      <Button variant="primary" type="submit" >
        Logga in
      </Button>
    </Form>
  );
}