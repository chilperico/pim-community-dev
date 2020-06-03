import {useState, useCallback, useEffect} from 'react';
import {Card} from 'akeneocommunicationchannel/models/card';
import {CardFetcher} from 'akeneocommunicationchannel/fetcher/card';

const useCards = (cardFetcher: CardFetcher): {cards: Card[] | null; fetchCards: () => Promise<void>} => {
  const [cards, setCards] = useState<Card[] | null>(null);

  const fetchCards = useCallback(async () => {
    setCards(await cardFetcher.fetchAll());
  }, [setCards]);

  useEffect(() => {
    fetchCards();
  }, []);

  return {cards, fetchCards};
};

export {useCards};
