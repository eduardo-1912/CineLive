package pt.ipleiria.estg.dei.amsi.cinelive.adapters;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Filme;

public class FilmesAdapter extends RecyclerView.Adapter<FilmesAdapter.FilmeViewHolder> {

    private List<Filme> filmes;
    private OnFilmeClickListener listener;

    public interface OnFilmeClickListener {
        void onFilmeClick(Filme filme);
    }

    public FilmesAdapter(List<Filme> filmes, OnFilmeClickListener listener) {
        this.filmes = filmes;
        this.listener = listener;
    }

    @NonNull
    @Override
    public FilmeViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View v = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_filme, parent, false);
        return new FilmeViewHolder(v);
    }

    @Override
    public void onBindViewHolder(@NonNull FilmeViewHolder holder, int position) {
        Filme filme = filmes.get(position);

        holder.tvTitulo.setText(filme.titulo);
        holder.imgPoster.setImageResource(filme.posterRes);

        holder.itemView.setOnClickListener(v -> listener.onFilmeClick(filme));
    }

    @Override
    public int getItemCount() {
        return filmes.size();
    }

    static class FilmeViewHolder extends RecyclerView.ViewHolder {
        ImageView imgPoster;
        TextView tvTitulo;

        public FilmeViewHolder(@NonNull View itemView) {
            super(itemView);
            imgPoster = itemView.findViewById(R.id.imgPoster);
            tvTitulo = itemView.findViewById(R.id.tvTituloFilme);
        }
    }
}