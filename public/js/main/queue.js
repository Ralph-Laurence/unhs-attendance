class Queue
{
    constructor()
    {
        this.items = {}
        this.frontIndex = 0
        this.backIndex = 0
        this.set = new Set()
    }
    // enqueue(item)
    // {
    //     if (typeof item !== 'string')
    //     {
    //         throw new Error('Data must be a string')
    //     }
    //     if (this.set.has(item))
    //     {
    //         return; //item + ' already exists'
    //     }
    //     this.items[this.backIndex] = item
    //     this.set.add(item)
    //     this.backIndex++
    //     return item; // + ' inserted'
    // }
    enqueue(item)
    {
        if (typeof item !== 'string')
        {
            throw new Error('Data must be a string')
        }
        if (this.contains(item))
        {
            return; //item + ' already exists'
        }
        this.items[this.backIndex] = { data: item, timestamp: Date.now() }
        this.set.add(item)
        this.backIndex++
        return item; // + ' inserted'
    }

    contains(item)
    {
        if (!this.set.has(item))
        {
            return false;
        }
        // Find the item in the queue and check its timestamp
        for (let i = this.frontIndex; i < this.backIndex; i++)
        {
            if (this.items[i].data === item)
            {
                // If the item was scanned more than 10 seconds ago, remove it from the queue
                if (Date.now() - this.items[i].timestamp > 10000)
                {
                    delete this.items[i];
                    this.set.delete(item);
                    return false;
                }
                return true;
            }
        }
        return false;
    }
    dequeue()
    {
        if (this.isEmpty())
        {
            // throw new Error('Queue is empty')
            return;
        }
        const item = this.items[this.frontIndex]
        delete this.items[this.frontIndex]
        this.set.delete(item)
        this.frontIndex++
        if (this.isEmpty())
        {
            this.frontIndex = 0
            this.backIndex = 0
        }
        return item
    }
    peek()
    {
        if (this.isEmpty())
        {
            throw new Error('Queue is empty')
        }
        return this.items[this.frontIndex]
    }
    // contains(item)
    // {
    //     return this.set.has(item)
    // }
    isEmpty()
    {
        return this.frontIndex === this.backIndex
    }
    size()
    {
        return this.backIndex - this.frontIndex
    }
    get printQueue()
    {
        return this.items
    }
}