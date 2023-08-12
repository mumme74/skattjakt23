import React, { useEffect, useReducer } from "react";
import { Link } from "react-router-dom";
import { appReducer, appState } from "../state/reducers";
import { useCustomContext } from "../state/custom.context";
import { Button } from "react-bootstrap";

export function Logout() {

  const {state, dispatch} = useCustomContext();
  const {userName} = state;

  const logout = ()=>{
    dispatch({type:'setLoginState', payload: false});
    dispatch({type:'setUserName', payload: ""});
  }

  return <>
    <h1>Logga ut</h1>
     {!userName ?
        <>
          Du har blivit utloggad!
          <Link to="/login">Logga in igen</Link>
        </> :
        <>
          <div>{userName} vill du logga ut?</div>
          <Button variant="primary" onClick={logout}>Logga ut</Button><br/>
          <Link to="/">Tillbaka till start</Link>
        </>
      }
  </>
}