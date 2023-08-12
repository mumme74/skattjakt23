import React, { useReducer } from 'react';
import logo from './logo.svg';
import { BrowserRouter, Route, Routes} from 'react-router-dom';
import { useState, createContext} from "react";
import { Header } from "./components/header/header.index";

import { MainMenuItems, MenuItemProps } from "./menuItems";

import { appState, AppState, AppAction, appReducer } from './state/reducers';

import { CustomContext, useCustomContext } from './state/custom.context';

import { Start } from './components/start';
import { Login } from './components/login';
import { Logout } from './components/logout';
import { StartQuiz } from './components/quiz/startQuiz';
import { TrackProgress } from './components/quiz/trackProgress';
import { Groups } from './components/quiz/groups';
import { NotFound } from './components/notFound';


import './App.css';



function App() {

  const [state, dispatch] = useReducer(appReducer, appState);

  window.addEventListener('unload', ()=>{
    localStorage.setItem('state', JSON.stringify(state));
  });

  const providerState = {
    state, dispatch
  };

  return (
    <div className="App">
      <CustomContext.Provider value={providerState}>
      <BrowserRouter>
        <Header />
          <Routes>
            <Route path="/" element={<Start />} />
            <Route path="/login" element={<Login />} />
            <Route path="/logout" element={<Logout />} />
            <Route path="/startQuiz" element={<StartQuiz />} />
            <Route path="/trackProgress" element={<TrackProgress />} />
            <Route path="/groups" element={<Groups />} />
            <Route index element={<Start />} />
            <Route path='*' element={<NotFound />} />
          </Routes>
      </BrowserRouter>
      </CustomContext.Provider>
    </div>
  );
}

export default App;
